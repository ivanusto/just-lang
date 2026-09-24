# Just Lang

[English](README.md)

給「每種語言各做一頁」的 WordPress 網站用的輕量多語系外掛。Just Lang 負責設定 `html lang`、`hreflang`、`og:locale` 與標題裡的站名，提供語言切換器，也能把初次來訪的訪客送到他們語言的版本，而且不會破壞整頁快取。

它**不會**改寫網址、翻譯字串、建資料表，也不管翻譯流程。各語言版本就是一般的頁面，網址由您自己決定；Just Lang 只負責告訴瀏覽器、搜尋引擎與社群平台這些頁面彼此的關係。

## 適合誰

* 每種語言只有幾頁的公司或產品網站，頁面常是用 HTML 區塊或頁面建構器手工做的。
* 放在會快取整頁的 CDN 後面的網站。這種網站若在伺服器端依語言轉址，轉址結果會被快取，所有訪客都會拿到同一個結果。
* 覺得 Polylang 或 WPML 太重，只想表達「這三頁是同一件事的三種語言」的人。

如果您需要自動產生的網址結構、佈景與外掛的字串翻譯或機器翻譯，請改用 Polylang、WPML 或 TranslatePress。偵測到它們啟用時，Just Lang 會顯示警告，因為兩者都會輸出 hreflang。

## 功能

* **每篇的語言與翻譯群組**：兩個 post meta，可在編輯畫面的語言方塊、REST API 或 WP-CLI 設定。
* **`<html lang>`**：設為該頁的語言。
* **hreflang**：列出同群組所有已發佈的頁面，並可設定 **x-default**。
* **og:locale**：輸出該頁的 locale，其他語言列為 **og:locale:alternate**。可搭配讀取站台 locale 的 SEO 外掛，並透過 `omni_og_locale` 直接與 [Omni Webmaster & SEO Suite](https://github.com/ivanusto/omni-webmaster-seo-suite) 整合。
* **各語言的站名**：例如英文與日文頁的標題改用英文品牌名。
* **語言切換器**：提供區塊、`[just_lang_switcher]` 短代碼與 `just_lang_switcher()` 樣板函式。
* **不怕快取的自動偵測**：在瀏覽器端執行（見下文）。
* **頁面摘要（選用）**：讓 SEO 外掛取得各語言自己的描述。
* **衝突警告**：Polylang、WPML、TranslatePress 或 Weglot 啟用時提示。

## 需求

* WordPress 6.0 以上
* PHP 7.4 以上

## 安裝

1. 從 [Releases](https://github.com/ivanusto/just-lang/releases) 下載 `just-lang-<版本>.zip`。
2. 在 WordPress 後台進入**外掛 > 安裝外掛 > 上傳外掛**，選擇 zip 檔後按**立即安裝**。
3. 啟用外掛。初次啟用會開啟站台語言與英文。
4. 到**設定 > Just Lang** 選擇語言、預設語言與 x-default。

## 使用方式

1. 每種語言建一頁，slug 與上層頁面隨意，例如 `/about/`、`/en/about/`、`/ja/about/`。
2. 在每頁的語言方塊設定**語言**，並填入相同的**翻譯群組**（例如 `about`）。
3. 發佈。同群組的每一頁都會把其他頁列為替代版本。

沒有設定語言的頁面視為預設語言。所以既有的單語網站只需要標記新增的其他語言頁面即可。

透過 REST API：

```bash
curl -u user:app-password -X POST https://example.com/wp-json/wp/v2/pages/42 \
  -H 'Content-Type: application/json' \
  -d '{"meta":{"_just_lang":"en","_just_lang_group":"about"}}'
```

透過 WP-CLI：

```bash
wp post meta update 42 _just_lang en
wp post meta update 42 _just_lang_group about
```

### 語言切換器

在樣板、樣板組件或頁面中加入 **Language Switcher** 區塊，或使用：

```
[just_lang_switcher style="inline" labels="name" current="1"]
```

* `style`：`inline`（橫排）或 `list`（清單）
* `labels`：`name`（English、日本語）或 `code`（EN、JA）
* `current`：`1` 顯示目前語言，`0` 隱藏

頁面沒有已發佈的翻譯時，切換器不輸出任何內容。

## 自動偵測的運作方式

`<head>` 裡一小段內嵌 script 會在頁面繪製前做判斷：

1. 爬蟲一律不轉向，所以每個語言版本都能被索引。
2. 從**您自己網站**的其他頁面進來的訪客一律不轉向，而是記住他正在看的語言。帶有 `?lang=keep` 的連結也一樣。
3. 從其他地方進來的訪客：如果這頁有他記住的語言就去那裡；否則依瀏覽器語言挑最接近的版本（先比完整標籤，再比同語言且繁簡相同，最後比同語言）；都沒有就去 x-default。

因為判斷發生在瀏覽器，伺服器對所有人送出相同的 HTML，CDN 每個網址只需快取一份。若在伺服器端依 `Accept-Language` 轉址，第一次的結果會被快取，之後所有訪客都拿到同一個。

可在**設定 > Just Lang** 關閉，或用 `just_lang_autodetect` 過濾器針對單頁關閉。

## 過濾器

* `just_lang_catalog( array $catalog )`：設定畫面提供的語言，格式為 `標籤 => [ 原文名稱, og:locale ]`。新增語言：`$catalog['ca'] = array( 'Català', 'ca_ES' );`。
* `just_lang_languages( array $languages )`：已啟用的語言，預設語言在最前面。
* `just_lang_post_types( string[] $types )`：帶有語言的文章類型，預設為附件以外的所有公開類型。
* `just_lang_x_default( string $tag, array $links )`：回應 x-default 的語言；回傳 `''` 則不輸出。
* `just_lang_site_name( string $name, string $tag )`：該語言標題中的站名；`''` 表示維持 WordPress 原本的標題。
* `just_lang_autodetect( bool $on, int $post_id )`：針對單頁關閉偵測。
* `just_lang_switch_locale( bool $on )`：Just Lang 會在 `wp_head` 輸出期間（優先序 1 到 99）把 `get_locale()` 切換成該頁的 locale，讓 SEO 外掛輸出正確的 `og:locale`。回傳 `false` 可停用。

## WordPress 核心與多語系

多語系是 Gutenberg 計畫預定的第四階段，但 [WordPress 路線圖](https://wordpress.org/about/roadmap/)目前沒有給出時程。核心推出自己的模型後，Just Lang 的語言標記部分就不再需要。資料刻意只存在兩個單純的 post meta（`_just_lang`、`_just_lang_group`），屆時遷移到核心應該相當直接。

## 解除安裝

解除安裝會刪除設定，但保留每篇文章的語言與群組，重新安裝後即可恢復。若也要刪除：

```bash
wp db query "DELETE FROM $(wp db prefix)postmeta WHERE meta_key IN ('_just_lang','_just_lang_group')"
```

## 授權

GPL-2.0-or-later，見 [LICENSE](LICENSE)。

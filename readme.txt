=== WebP Resim Optimizer ===
Contributors: ridvanay
Tags: webp, image optimizer, resim sikistirma, image compression, webp converter
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Yüklenen tüm görselleri (WebP dahil) otomatik olarak akıllı algoritmalarla WebP formatında sıkıştırır ve hedef dosya boyutuna ulaştırır.

== Description ==

**WebP Resim Optimizer**, WordPress sitenizin hızını ve performansını artırmak için geliştirilmiş akıllı bir görsel optimizasyon eklentisidir. Geleneksel eklentilerin aksine, sadece sabit bir kalitede dönüştürme yapmaz; belirlediğiniz hedef dosya boyutuna (KB) ulaşana kadar görsel kalitesini ve çözünürlüğünü kademeli olarak optimize eder.

Eklenti hem **GD** hem de **ImageMagick (Imagick)** kütüphanelerini tam uyumlu olarak destekler.

### Öne Çıkan Özellikler:
* **Akıllı Hedef Boyut Algoritması:** Görsel kalitesini, sizin belirlediğiniz maksimum KB sınırına ulaşana kadar otomatik olarak optimize eder.
* **Tam WebP Dönüşümü:** JPEG, PNG, GIF ve BMP formatındaki tüm görselleri otomatik olarak yeni nesil WebP formatına çevirir.
* **Thumbnail (Küçük Resim) Desteği:** Sadece ana görselleri değil, WordPress'in oluşturduğu tüm alt boyutları da sıkıştırır.
* **Genişlik Sınırlandırma:** Çok büyük çözünürlüklü görselleri yükleme anında otomatik olarak maksimum genişliğe (örn. 1920px) ölçekler.
* **Manuel Sıkıştırma (Gelişmiş):** Ortam kütüphanesindeki eski görselleri Attachment ID kullanarak yönetim panelinden manuel olarak optimize edebilirsiniz.
* **Orijinal Yedekleme:** İsteğe bağlı olarak orijinal görsellerinizi `.bak` uzantısıyla sunucunuzda güvenle saklar.

> Bu proje bir [Rıdvan AY](https://www.ridvanay.com) iştirakidir ve [Palmiye Ahşap Dekorasyon](https://www.palmiyeahsapdekorasyon.com) altyapısı ile desteklenmektedir.

== Installation ==

1. Eklenti klasörünü `/wp-content/plugins/` dizinine yükleyin veya WordPress panelinden "Yeni Ekle > Eklenti Yükle" adımlarını takip edin.
2. Eklentiyi aktifleştirin.
3. WordPress yönetim panelinizden **Ayarlar > WebP Optimizer** sayfasına giderek hedef boyut ve kalite sınırlarınızı belirleyin.

== Frequently Asked Questions ==

= Eklenti ücretsiz mi? =
Evet, eklenti tamamen ücretsizdir ve herhangi bir üçüncü parti API anahtarı (Cloudflare, TinyPNG vb.) gerektirmeden doğrudan kendi sunucunuzun gücünü kullanır.

= Sunucumda hangi kütüphaneler olmalı? =
Eklentinin çalışabilmesi için sunucunuzda GD kütüphanesi (WebP destekli) veya ImageMagick kütüphanesi aktif olmalıdır. Ayarlar sayfasından sunucu gereksinimlerinizi anlık olarak kontrol edebilirsiniz.

== Changelog ==

= 2.0.0 =
* Akıllı KB hedefleme algoritması eklendi.
* Manuel Attachment ID ile sıkıştırma özelliği eklendi.
* Thumbnail optimizasyonu entegre edildi.
* İlk kararlı sürüm yayınlandı.
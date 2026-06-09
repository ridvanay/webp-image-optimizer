<?php
/**
 * Plugin Name: WebP Resim Optimizer
 * Plugin URI:  https://www.ridvanay.com
 * Description: Yüklenen tüm görselleri (WebP dahil) otomatik olarak WebP formatında sıkıştırır ve hedef dosya boyutuna ulaşır.
 * Version:     2.0.0
 * Author:      Rıdvan AY (Palmiye Ahşap Dekorasyon)
 * Author URI:  https://www.palmiyeahsapdekorasyon.com
 * License:     GPL2
 * Text Domain: webp-resim-optimizer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WIO_VERSION',    '2.0.0' );
define( 'WIO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/* ---------------------------------------------------------------
 * 1. AKTİVASYON
 * ------------------------------------------------------------- */
register_activation_hook( __FILE__, 'wio_activate' );
function wio_activate() {
    if ( false === get_option( 'wio_settings' ) ) {
        update_option( 'wio_settings', [
            'max_size_kb'   => 64,
            'quality_start' => 82,
            'quality_min'   => 5,
            'max_width'     => 1920,
            'keep_original' => false,
        ] );
    }
}

/* ---------------------------------------------------------------
 * 2. AYAR SAYFASI
 * ------------------------------------------------------------- */
add_action( 'admin_menu', 'wio_add_menu' );
function wio_add_menu() {
    add_options_page(
        'WebP Resim Optimizer',
        'WebP Optimizer',
        'manage_options',
        'webp-resim-optimizer',
        'wio_settings_page'
    );
}

add_action( 'admin_init', 'wio_register_settings' );
function wio_register_settings() {
    register_setting( 'wio_settings_group', 'wio_settings', 'wio_sanitize_settings' );
}

function wio_sanitize_settings( $input ) {
    return [
        'max_size_kb'   => max( 5,   intval( $input['max_size_kb']   ?? 64   ) ),
        'quality_start' => min( 100, max( 10, intval( $input['quality_start'] ?? 82 ) ) ),
        'quality_min'   => min( 30,  max( 1,  intval( $input['quality_min']   ?? 5  ) ) ),
        'max_width'     => max( 200, intval( $input['max_width']      ?? 1920 ) ),
        'keep_original' => ! empty( $input['keep_original'] ),
    ];
}

function wio_settings_page() {
    $opts  = get_option( 'wio_settings', [] );
    $saved = isset( $_GET['settings-updated'] ) && $_GET['settings-updated'];
    ?>
    <div class="wrap">
        <h1>🖼️ WebP Image Optimizer <span style="font-size:13px;color:#666;">v2.0</span></h1>
        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p>Ayarlar kaydedildi.</p></div>
        <?php endif; ?>
        <p>Yüklenen <strong>tüm</strong> görseller (WebP dahil) otomatik sıkıştırılır ve hedef boyuta indirilir.</p>

        <form method="post" action="options.php">
            <?php settings_fields( 'wio_settings_group' ); ?>
            <table class="form-table">

                <tr>
                    <th><label for="max_size_kb">🎯 Hedef Maksimum Boyut (KB)</label></th>
                    <td>
                        <input type="number" id="max_size_kb" name="wio_settings[max_size_kb]"
                               value="<?php echo esc_attr( $opts['max_size_kb'] ?? 64 ); ?>"
                               min="5" max="10240" class="small-text" />
                        <p class="description">Eklenti bu boyutun altına inene kadar kaliteyi ve çözünürlüğü düşürür.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="quality_start">Başlangıç Kalitesi (%)</label></th>
                    <td>
                        <input type="number" id="quality_start" name="wio_settings[quality_start]"
                               value="<?php echo esc_attr( $opts['quality_start'] ?? 82 ); ?>"
                               min="10" max="100" class="small-text" />
                        <p class="description">İlk deneme kalitesi. Buradan başlayarak aşağı iner.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="quality_min">Minimum Kalite (%)</label></th>
                    <td>
                        <input type="number" id="quality_min" name="wio_settings[quality_min]"
                               value="<?php echo esc_attr( $opts['quality_min'] ?? 5 ); ?>"
                               min="1" max="30" class="small-text" />
                        <p class="description">Kalite bu noktaya düşerse sonraki adımda görsel boyutu küçültülür.</p>
                    </td>
                </tr>

                <tr>
                    <th><label for="max_width">Maksimum Genişlik (px)</label></th>
                    <td>
                        <input type="number" id="max_width" name="wio_settings[max_width]"
                               value="<?php echo esc_attr( $opts['max_width'] ?? 1920 ); ?>"
                               min="200" max="6000" class="small-text" />
                        <p class="description">Bu genişliği aşan görseller önce buna ölçeklenir.</p>
                    </td>
                </tr>

                <tr>
                    <th>Orijinal Dosyayı Koru</th>
                    <td>
                        <label>
                            <input type="checkbox" name="wio_settings[keep_original]" value="1"
                                   <?php checked( ! empty( $opts['keep_original'] ) ); ?> />
                            Evet, orijinali <code>.bak</code> uzantısıyla sakla
                        </label>
                    </td>
                </tr>

            </table>
            <?php submit_button( 'Ayarları Kaydet' ); ?>
        </form>

        <hr>
        <h2>Sunucu Gereksinimleri</h2>
        <?php wio_show_requirements(); ?>

        <hr>
        <h2>Mevcut Görseli Manuel Sıkıştır</h2>
        <p>Attachment ID girin ve sıkıştır düğmesine tıklayın:</p>
        <input type="number" id="wio_manual_id" placeholder="örn. 123" class="regular-text" />
        <button class="button button-secondary" onclick="wioManualCompress()">Sıkıştır</button>
        <span id="wio_manual_result" style="margin-left:10px;font-weight:600;"></span>
        <script>
        function wioManualCompress() {
            var id  = document.getElementById('wio_manual_id').value;
            var res = document.getElementById('wio_manual_result');
            if (!id) { res.textContent = 'Lütfen bir ID girin.'; return; }
            res.textContent = 'İşleniyor…';
            var fd = new FormData();
            fd.append('action', 'wio_manual_compress');
            fd.append('attachment_id', id);
            fd.append('nonce', '<?php echo wp_create_nonce("wio_manual"); ?>');
            fetch(ajaxurl, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => { res.textContent = d.success ? d.data : '❌ ' + d.data; });
        }
        </script>
    </div>
    <?php
}

function wio_show_requirements() {
    $gd      = extension_loaded( 'gd' );
    $webp_gd = $gd && function_exists( 'imagewebp' );
    $imagick = extension_loaded( 'imagick' );
    $webp_im = false;
    if ( $imagick ) {
        try { $webp_im = (bool)( new Imagick() )->queryFormats( 'WEBP' ); } catch ( Exception $e ) {}
    }
    echo '<ul>';
    echo '<li>' . ( $gd      ? '✅' : '❌' ) . ' GD kütüphanesi</li>';
    echo '<li>' . ( $webp_gd ? '✅' : '❌' ) . ' GD WebP desteği</li>';
    echo '<li>' . ( $imagick ? '✅' : '❌' ) . ' ImageMagick kütüphanesi</li>';
    echo '<li>' . ( $webp_im ? '✅' : '❌' ) . ' ImageMagick WebP desteği</li>';
    echo '</ul>';
    if ( ! $webp_gd && ! $webp_im ) {
        echo '<div class="notice notice-error inline"><p><strong>Uyarı:</strong> WebP desteği bulunamadı. Hosting sağlayıcınızla iletişime geçin.</p></div>';
    }
}

/* ---------------------------------------------------------------
 * 3. ANA HOOK — wp_handle_upload
 *    WebP dahil HER görseli yakalar
 * ------------------------------------------------------------- */
add_filter( 'wp_handle_upload', 'wio_handle_upload' );
function wio_handle_upload( $upload ) {
    if ( strpos( $upload['type'], 'image/' ) === false ) {
        return $upload;
    }
    $skip = [ 'image/svg+xml', 'image/x-icon', 'image/vnd.microsoft.icon' ];
    if ( in_array( $upload['type'], $skip, true ) ) {
        return $upload;
    }

    $result = wio_process_image( $upload['file'], $upload['type'] );

    if ( $result ) {
        if ( $result['path'] !== $upload['file'] ) {
            $upload['url'] = str_replace(
                wp_basename( $upload['file'] ),
                wp_basename( $result['path'] ),
                $upload['url']
            );
            $upload['file'] = $result['path'];
        }
        $upload['type'] = 'image/webp';
    }

    return $upload;
}

/* ---------------------------------------------------------------
 * 4. GÖRSEL İŞLEME
 * ------------------------------------------------------------- */
function wio_process_image( $source_path, $mime ) {
    $opts        = get_option( 'wio_settings', [] );
    $max_bytes   = intval( $opts['max_size_kb']   ?? 64  ) * 1024;
    $quality     = intval( $opts['quality_start'] ?? 82  );
    $quality_min = intval( $opts['quality_min']   ?? 5   );
    $max_width   = intval( $opts['max_width']     ?? 1920 );
    $keep_orig   = ! empty( $opts['keep_original'] );

    $webp_path = preg_replace( '/\.[^.\/]+$/', '.webp', $source_path );

    $use_imagick = extension_loaded( 'imagick' );
    $use_gd      = extension_loaded( 'gd' ) && function_exists( 'imagewebp' );
    if ( ! $use_imagick && ! $use_gd ) {
        return false;
    }

    $info = @getimagesize( $source_path );
    if ( ! $info ) { return false; }

    $orig_w        = $info[0];
    $current_width = min( $orig_w, $max_width );

    // Adım 1: Kaliteyi düşür
    $blob = wio_render_webp( $source_path, $mime, $quality, $current_width, $use_imagick );
    while ( $blob !== false && strlen( $blob ) > $max_bytes && $quality > $quality_min ) {
        $quality -= 5;
        $blob = wio_render_webp( $source_path, $mime, $quality, $current_width, $use_imagick );
    }

    // Adım 2: Boyutu da küçült
    if ( $blob !== false && strlen( $blob ) > $max_bytes ) {
        $step_w = $current_width;
        while ( strlen( $blob ) > $max_bytes && $step_w > 200 ) {
            $step_w = intval( $step_w * 0.75 );
            $blob   = wio_render_webp( $source_path, $mime, $quality, $step_w, $use_imagick );
        }
    }

    if ( $blob === false || empty( $blob ) ) { return false; }

    if ( $keep_orig ) {
        @copy( $source_path, $source_path . '.bak' );
    }

    if ( false === file_put_contents( $webp_path, $blob ) ) {
        return false;
    }

    if ( $webp_path !== $source_path ) {
        @unlink( $source_path );
    }

    return [ 'path' => $webp_path ];
}

/* ---------------------------------------------------------------
 * 5. RENDER HELPERs
 * ------------------------------------------------------------- */
function wio_render_webp( $src, $mime, $quality, $target_width, $use_imagick ) {
    if ( $use_imagick ) {
        return wio_render_imagick( $src, $quality, $target_width );
    }
    return wio_render_gd( $src, $mime, $quality, $target_width );
}

function wio_render_imagick( $src, $quality, $target_width ) {
    try {
        $im = new Imagick( $src );
        $im->setImageFormat( 'webp' );
        $im->stripImage();
        $im->setImageCompressionQuality( $quality );
        if ( $im->getImageWidth() > $target_width ) {
            $im->resizeImage( $target_width, 0, Imagick::FILTER_LANCZOS, 1 );
        }
        $blob = $im->getImagesBlob();
        $im->destroy();
        return $blob;
    } catch ( Exception $e ) {
        return false;
    }
}

function wio_render_gd( $src, $mime, $quality, $target_width ) {
    switch ( $mime ) {
        case 'image/jpeg': $img = @imagecreatefromjpeg( $src ); break;
        case 'image/png':  $img = @imagecreatefrompng( $src );  break;
        case 'image/gif':  $img = @imagecreatefromgif( $src );  break;
        case 'image/bmp':  $img = @imagecreatefrombmp( $src );  break;
        case 'image/webp': $img = @imagecreatefromwebp( $src ); break;
        default: return false;
    }
    if ( ! $img ) { return false; }

    $ow = imagesx( $img );
    $oh = imagesy( $img );

    if ( $ow > $target_width ) {
        $nh      = intval( $oh * $target_width / $ow );
        $resized = imagecreatetruecolor( $target_width, $nh );
        imagealphablending( $resized, false );
        imagesavealpha( $resized, true );
        $trans = imagecolorallocatealpha( $resized, 0, 0, 0, 127 );
        imagefilledrectangle( $resized, 0, 0, $target_width, $nh, $trans );
        imagecopyresampled( $resized, $img, 0, 0, 0, 0, $target_width, $nh, $ow, $oh );
        imagedestroy( $img );
        $img = $resized;
    }

    ob_start();
    imagewebp( $img, null, $quality );
    $blob = ob_get_clean();
    imagedestroy( $img );
    return $blob;
}

/* ---------------------------------------------------------------
 * 6. THUMBNAIL'LERİ DE SIKIŞTIR
 * ------------------------------------------------------------- */
add_filter( 'wp_generate_attachment_metadata', 'wio_compress_thumbnails', 20, 2 );
function wio_compress_thumbnails( $metadata, $attachment_id ) {
    $opts      = get_option( 'wio_settings', [] );
    $max_bytes = intval( $opts['max_size_kb'] ?? 64 ) * 1024;

    $upload_dir = wp_upload_dir();
    $base_dir   = trailingslashit( $upload_dir['basedir'] );
    $sub_dir    = isset( $metadata['file'] ) ? dirname( $metadata['file'] ) : '';
    $dir        = $base_dir . trailingslashit( $sub_dir );

    if ( empty( $metadata['sizes'] ) ) { return $metadata; }

    foreach ( $metadata['sizes'] as $size_data ) {
        $thumb_path = $dir . $size_data['file'];
        if ( ! file_exists( $thumb_path ) ) { continue; }
        $mime = $size_data['mime-type'] ?? mime_content_type( $thumb_path );
        if ( strpos( $mime, 'image/' ) === false ) { continue; }

        $info = @getimagesize( $thumb_path );
        if ( ! $info ) { continue; }

        $use_imagick = extension_loaded( 'imagick' );
        $quality     = intval( $opts['quality_start'] ?? 82 );
        $quality_min = intval( $opts['quality_min']   ?? 5  );
        $w           = $info[0];

        $blob = wio_render_webp( $thumb_path, $mime, $quality, $w, $use_imagick );
        while ( $blob !== false && strlen( $blob ) > $max_bytes && $quality > $quality_min ) {
            $quality -= 5;
            $blob = wio_render_webp( $thumb_path, $mime, $quality, $w, $use_imagick );
        }
        if ( $blob ) {
            file_put_contents( $thumb_path, $blob );
        }
    }

    wp_update_post( [ 'ID' => $attachment_id, 'post_mime_type' => 'image/webp' ] );
    return $metadata;
}

/* ---------------------------------------------------------------
 * 7. MANUEL AJAX
 * ------------------------------------------------------------- */
add_action( 'wp_ajax_wio_manual_compress', 'wio_ajax_manual_compress' );
function wio_ajax_manual_compress() {
    check_ajax_referer( 'wio_manual', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Yetki yok.' );
    }
    $id   = intval( $_POST['attachment_id'] ?? 0 );
    $file = get_attached_file( $id );
    if ( ! $file || ! file_exists( $file ) ) {
        wp_send_json_error( "Dosya bulunamadı (ID: $id)" );
    }
    $mime   = get_post_mime_type( $id );
    $before = round( filesize( $file ) / 1024, 1 );
    $result = wio_process_image( $file, $mime );
    if ( $result ) {
        update_attached_file( $id, $result['path'] );
        $after = round( filesize( $result['path'] ) / 1024, 1 );
        wp_send_json_success( "✅ {$before} KB → {$after} KB" );
    } else {
        wp_send_json_error( "❌ Dönüştürme başarısız (ID: $id)" );
    }
}

/* ---------------------------------------------------------------
 * 8. MIME İZİNLERİ
 * ------------------------------------------------------------- */
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
} );

add_filter( 'file_is_displayable_image', function( $result, $path ) {
    return ( strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) === 'webp' ) ? true : $result;
}, 10, 2 );

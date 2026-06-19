jQuery(document).ready(function($) {
  'use strict';

  // 1. QR Code Generation for Detail Page
  var $qrcodeContainer = $('#booking-qrcode');
  if ($qrcodeContainer.length && typeof QRCode !== 'undefined') {
    var bookingUrl = $qrcodeContainer.attr('data-booking-url');
    var itemName = $qrcodeContainer.attr('data-item-name') || 'item';
    
    // Generate QR Code
    var qrcode = new QRCode($qrcodeContainer[0], {
      text: bookingUrl,
      width: 160,
      height: 160,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });

    // Handle Download button
    $('#btn-download-qrcode').on('click', function(e) {
      e.preventDefault();
      var img = $qrcodeContainer.find('img').attr('src');
      if (!img) {
        var canvas = $qrcodeContainer.find('canvas')[0];
        if (canvas) {
          img = canvas.toDataURL("image/png");
        }
      }
      if (img) {
        var link = document.createElement('a');
        link.href = img;
        link.download = 'booking-qrcode-' + sanitizeTitle(itemName) + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else {
        alert('Gagal mengunduh QR Code. Silakan coba lagi.');
      }
    });
  }

  // 2. WP Media Library Uploader for Tambah Form
  $(document).on('click', '#btn-add-pilih-gambar', function(e) {
    e.preventDefault();
    if (typeof wp === 'undefined' || !wp.media) return;
    var frame = wp.media({
      title: 'Pilih Gambar',
      multiple: false,
      library: { type: 'image' },
      button: { text: 'Gunakan Gambar' }
    });
    frame.on('select', function() {
      var attachment = frame.state().get('selection').first().toJSON();
      $('#add-gambar-url').val(attachment.url);
    });
    frame.open();
  });

  // 3. WP Media Library Uploader for Edit Form
  $(document).on('click', '#btn-edit-pilih-gambar', function(e) {
    e.preventDefault();
    if (typeof wp === 'undefined' || !wp.media) return;
    var frame = wp.media({
      title: 'Pilih Gambar',
      multiple: false,
      library: { type: 'image' },
      button: { text: 'Gunakan Gambar' }
    });
    frame.on('select', function() {
      var attachment = frame.state().get('selection').first().toJSON();
      $('#edit-gambar-url').val(attachment.url);
      $('#edit-gambar-preview').attr('src', attachment.url);
      $('#edit-gambar-preview-container').show();
    });
    frame.open();
  });

  // Helper to sanitize title for filename
  function sanitizeTitle(string) {
    return string
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
});

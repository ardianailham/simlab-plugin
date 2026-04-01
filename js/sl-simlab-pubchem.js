/**
 * sl-simlab-pubchem.js
 * Fetches PubChem data for a chemical (bahan) and renders an info panel.
 *
 * Triggered automatically when the page contains a `[data-pubchem-name]`
 * attribute, which is injected by the detail and booking PHP views.
 *
 * Requires: sl_simlab_pubchem (localised object with `ajax_url` and `nonce`)
 */
(function ($) {
  'use strict';

  /* ── GHS signal-word colour map ────────────────────────────────────── */
  var SIGNAL_COLORS = {
    danger:  { bg: '#fff0f0', border: '#e53935', text: '#b71c1c' },
    warning: { bg: '#fffde7', border: '#f9a825', text: '#5d4037' },
    default: { bg: '#e8f5e9', border: '#43a047', text: '#1b5e20' },
  };

  function signalStyle(word) {
    var w = (word || '').toLowerCase();
    if (w === 'danger')  return SIGNAL_COLORS.danger;
    if (w === 'warning') return SIGNAL_COLORS.warning;
    return SIGNAL_COLORS.default;
  }

  /* ── Build the HTML panel ───────────────────────────────────────────── */
  function buildPanel(data) {
    var h = data.hazard;
    var signalWord = h ? (h.signal_word || '') : '';
    var col = signalStyle(signalWord);

    /* --- Header row: structure image + key identifiers --- */
    var headerHtml =
      '<div style="display:flex;gap:18px;flex-wrap:wrap;align-items:flex-start;margin-bottom:14px;">' +
        '<div style="flex-shrink:0;">' +
          '<img src="' + esc(data.structure_png) + '" alt="' + esc(data.iupac_name) + '"' +
          ' style="width:140px;height:140px;object-fit:contain;border:1px solid #dee2e6;border-radius:8px;background:#fff;padding:4px;">' +
        '</div>' +
        '<div style="flex:1;min-width:180px;">' +
          '<p style="margin:0 0 4px;font-size:13px;color:#6c757d;">IUPAC Name</p>' +
          '<p style="margin:0 0 10px;font-size:15px;font-weight:600;color:#212529;">' + esc(data.iupac_name || '—') + '</p>' +
          '<div style="display:flex;flex-wrap:wrap;gap:8px;">' +
            metricBadge('Formula',    data.formula) +
            metricBadge('Mol. Weight', data.molecular_weight ? data.molecular_weight + ' g/mol' : '—') +
            metricBadge('CID',        data.cid) +
          '</div>' +
          '<div style="margin-top:10px;">' +
            '<a href="' + esc(data.pubchem_url) + '" target="_blank" rel="noopener"' +
            ' style="font-size:12px;color:#0d6efd;text-decoration:none;">' +
            '<i class="fa fa-external-link" style="margin-right:4px;"></i>Lihat di PubChem</a>' +
          '</div>' +
        '</div>' +
      '</div>';

    /* --- SMILES row --- */
    var smilesHtml =
      '<div style="background:#f8f9fa;border-radius:6px;padding:8px 12px;margin-bottom:12px;">' +
        '<span style="font-size:11px;font-weight:600;color:#6c757d;text-transform:uppercase;letter-spacing:.5px;">SMILES</span>' +
        '<div style="font-family:\'Courier New\',monospace;font-size:12px;color:#212529;word-break:break-all;margin-top:2px;">' +
          esc(data.smiles || '—') +
        '</div>' +
      '</div>';

    /* --- Hazard section --- */
    var hazardHtml = '';
    if (h) {
      /* All pictogram icons */
      var iconHtml = '';
      var icons = h.all_pictograms || (h.pictogram_url ? [h.pictogram_url] : []);
      for (var i = 0; i < icons.length; i++) {
        iconHtml +=
          '<img src="' + esc(icons[i]) + '" alt="GHS pictogram"' +
          ' style="width:52px;height:52px;margin-right:6px;margin-bottom:4px;" onerror="this.style.display=\'none\'">';
      }

      /* Signal word badge */
      var swBadge = signalWord
        ? '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;' +
          'background:' + col.border + ';color:#fff;margin-left:8px;">' + esc(signalWord) + '</span>'
        : '';

      /* Hazard statements list */
      var stmts = h.all_statements || (h.statement ? [h.statement] : []);
      var stmtHtml = '';
      for (var j = 0; j < stmts.length && j < 5; j++) {
        stmtHtml +=
          '<li style="font-size:13px;color:#495057;margin-bottom:3px;">' + esc(stmts[j]) + '</li>';
      }

      hazardHtml =
        '<div style="border:1px solid ' + col.border + ';border-left:4px solid ' + col.border + ';' +
        'border-radius:6px;background:' + col.bg + ';padding:12px 14px;">' +
          '<div style="display:flex;align-items:center;margin-bottom:8px;">' +
            '<span style="font-size:13px;font-weight:700;color:' + col.text + ';">' +
              '<i class="fa fa-exclamation-triangle" style="margin-right:6px;"></i>Bahaya Utama (GHS)' +
            '</span>' +
            swBadge +
          '</div>' +
          (iconHtml ? '<div style="margin-bottom:8px;">' + iconHtml + '</div>' : '') +
          (stmtHtml ? '<ul style="margin:0;padding-left:18px;">' + stmtHtml + '</ul>' : '') +
        '</div>';
    } else {
      hazardHtml =
        '<div style="font-size:13px;color:#6c757d;padding:8px 0;">' +
          '<i class="fa fa-info-circle" style="margin-right:5px;"></i>' +
          'Data bahaya GHS tidak tersedia untuk senyawa ini.' +
        '</div>';
    }

    return headerHtml + smilesHtml + hazardHtml;
  }

  function metricBadge(label, value) {
    return '<div style="background:#fff;border:1px solid #dee2e6;border-radius:6px;padding:5px 10px;min-width:90px;">' +
      '<div style="font-size:10px;color:#6c757d;font-weight:600;">' + esc(label) + '</div>' +
      '<div style="font-size:13px;font-weight:600;color:#212529;">' + esc(String(value || '—')) + '</div>' +
    '</div>';
  }

  /* ── Safety escape (plain text only, not for HTML attributes with quotes) */
  function esc(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /* ── Init ───────────────────────────────────────────────────────────── */
  /* ── Init function ── */
  function doLookup($panel) {
    var $container = $($panel);
    var compoundName = $container.attr('data-pubchem-name') || $container.data('pubchem-name');
    
    if (!compoundName) {
      $container.html(
        '<p style="color:#6c757d;font-size:13px;">' +
        '<i class="fa fa-info-circle"></i> Nama bahan tidak tersedia untuk pencarian PubChem.</p>'
      );
      return;
    }

    /* Show skeleton / loading state */
    $container.html(
      '<div style="display:flex;align-items:center;gap:10px;padding:12px 0;color:#6c757d;font-size:14px;">' +
        '<span class="fa fa-spinner fa-spin"></span>' +
        '<span>Memuat data PubChem untuk <strong>' + esc(compoundName) + '</strong>…</span>' +
      '</div>'
    );

    $.ajax({
      url:      sl_simlab_pubchem.ajax_url,
      method:   'GET',
      data: {
        action: 'sl_pubchem_lookup',
        name:   compoundName,
      },
      success: function (resp) {
        if (resp.success) {
          $container.html(buildPanel(resp.data));
        } else {
          $container.html(notFound(compoundName, resp.data && resp.data.message));
        }
      },
      error: function () {
        $container.html(notFound(compoundName, 'Koneksi ke server gagal.'));
      },
    });
  }

  /* ── Init ───────────────────────────────────────────────────────────── */
  function init() {
    // Auto-init panels with [data-pubchem-panel]
    $('[data-pubchem-panel]').each(function() {
      doLookup(this);
    });
  }

  function notFound(name, msg) {
    return '<div style="color:#856404;background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;font-size:13px;">' +
      '<i class="fa fa-exclamation-circle" style="margin-right:6px;"></i>' +
      '<strong>' + esc(name) + '</strong> tidak ditemukan di PubChem.' +
      (msg ? ' <span style="color:#6c757d;">(' + esc(msg) + ')</span>' : '') +
    '</div>';
  }

  // Expose to window for manual triggers
  window.triggerPubChemLookup = doLookup;

  $(document).ready(init);

}(jQuery));

<?php
defined('ABSPATH') || exit;

/**
 * Admin – List & Export Form Entries
 */

/* =====================================================
 * Permission Check
 * ===================================================== */
if (!current_user_can('manage_options')) {
  wp_die(__('You do not have permission to access this page.', 'dynamic-form'));
}

/* =====================================================
 * Handle Export (JSON / CSV)
 * IMPORTANT: Runs BEFORE any HTML output
 * ===================================================== */
if (
  isset($_GET['page'], $_GET['export'], $_GET['_wpnonce']) &&
  $_GET['page'] === 'df-entries' &&
  wp_verify_nonce($_GET['_wpnonce'], 'df_export_entries')
) {

  $export  = sanitize_text_field($_GET['export']);
  $entries = DF_FormRepository::get_all_entries(10000);

  // -------- JSON EXPORT --------
  if ($export === 'json') {
    nocache_headers();
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=df-entries.json');

    echo wp_json_encode($entries, JSON_PRETTY_PRINT);
    exit;
  }

  // -------- CSV (EXCEL) EXPORT --------
  if ($export === 'csv') {
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=df-entries.csv');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM (fixes Excel encoding issues)
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // CSV Header
    fputcsv($output, [
      'Entry ID',
      'Form ID',
      'Field',
      'Value',
      'IP Address',
      'User Agent',
      'Date'
    ]);

    foreach ($entries as $entry) {
      $data = json_decode($entry['data'], true) ?: [];

      foreach ($data as $field => $value) {
        fputcsv($output, [
          $entry['id'],
          $entry['form_id'],
          $field,
          is_array($value) ? implode(', ', $value) : $value,
          $entry['ip_address'],
          $entry['user_agent'],
          $entry['created_at'],
        ]);
      }
    }

    fclose($output);
    exit;
  }
}

/* =====================================================
 * Pagination Setup
 * ===================================================== */
$per_page = 20;

$current_page = isset($_GET['paged'])
  ? max(1, absint($_GET['paged']))
  : 1;

$offset = ($current_page - 1) * $per_page;

$total_entries = DF_FormRepository::count_entries();
$total_pages   = (int) ceil($total_entries / $per_page);

$entries = DF_FormRepository::get_all_entries($per_page, $offset);

// Export nonce
$export_nonce = wp_create_nonce('df_export_entries');
?>

<div class="wrap">
  <h1><?php esc_html_e('Form Submissions', 'dynamic-form'); ?></h1>

  <div style="margin: 15px 0;">
    <button
      type="button"
      onclick="directDownload('<?php echo esc_url(admin_url("admin.php?page=df-entries&export=json&_wpnonce=$export_nonce")); ?>')"
      class="button button-secondary">
      <?php esc_html_e('Export JSON', 'dynamic-form'); ?>
    </button>

    <button
      type="button"
      onclick="directDownload('<?php echo esc_url(admin_url("admin.php?page=df-entries&export=csv&_wpnonce=$export_nonce")); ?>')"
      class="button button-secondary">
      <?php esc_html_e('Export CSV', 'dynamic-form'); ?>
    </button>
  </div>

  <table class="widefat fixed striped">
    <thead>
      <tr>
        <th><?php esc_html_e('ID', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Form ID', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Submission Data', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('IP Address', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Date', 'dynamic-form'); ?></th>
        <th><?php esc_html_e('Action', 'dynamic-form'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php if (empty($entries)) : ?>
        <tr>
          <td colspan="6"><?php esc_html_e('No entries found.', 'dynamic-form'); ?></td>
        </tr>
      <?php else : ?>
        <?php foreach ($entries as $entry) : ?>
          <tr>
            <td><?php echo esc_html($entry['id']); ?></td>
            <td><?php echo esc_html($entry['form_id']); ?></td>
            <td>
              <pre style="white-space: pre-wrap; max-width: 500px;">
<?php
          echo esc_html(
            wp_json_encode(
              json_decode($entry['data'], true),
              JSON_PRETTY_PRINT
            )
          );
?>
              </pre>
            </td>
            <td><?php echo esc_html($entry['ip_address']); ?></td>
            <td><?php echo esc_html($entry['created_at']); ?></td>
            <td>
              <a href="#" class="df-delete-entry" data-id="<?php echo esc_attr($entry['id']); ?>">
                <?php esc_html_e('Delete', 'dynamic-form'); ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1) : ?>
    <div class="tablenav bottom">
      <div class="tablenav-pages">
        <span class="displaying-num">
          <?php echo esc_html($total_entries); ?> items
        </span>

        <span class="pagination-links">
          <?php
          $base_url = remove_query_arg(['paged'], $_SERVER['REQUEST_URI']);

          if ($current_page > 1) {
            printf(
              '<a class="prev-page button" href="%s">&lsaquo;</a>',
              esc_url(add_query_arg('paged', $current_page - 1, $base_url))
            );
          } else {
            echo '<span class="tablenav-pages-navspan button disabled">&lsaquo;</span>';
          }

          echo '<span class="paging-input">';
          echo esc_html($current_page) . ' of ' . esc_html($total_pages);
          echo '</span>';

          if ($current_page < $total_pages) {
            printf(
              '<a class="next-page button" href="%s">&rsaquo;</a>',
              esc_url(add_query_arg('paged', $current_page + 1, $base_url))
            );
          } else {
            echo '<span class="tablenav-pages-navspan button disabled">&rsaquo;</span>';
          }
          ?>
        </span>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
  document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('df-delete-entry')) return;

    e.preventDefault();

    if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this entry?', 'dynamic-form')); ?>')) {
      return;
    }

    const entryId = e.target.dataset.id;

    const data = new FormData();
    data.append('action', 'df_delete_entry');
    data.append('entry_id', entryId);
    data.append('_ajax_nonce', dfAjax.nonce);

    fetch(dfAjax.ajax_url, {
        method: 'POST',
        body: data
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          location.reload();
        } else {
          alert(json.data?.message || 'Delete failed');
        }
      })
      .catch(() => {
        alert('Network error. Please try again.');
      });
  });

  function directDownload(url) {
    const a = document.createElement('a');
    a.href = url;
    document.body.appendChild(a);
    a.click();
    a.remove();
  }
</script>

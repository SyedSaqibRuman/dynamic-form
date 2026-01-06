<?php
defined('ABSPATH') || exit;

$forms = DF_FormRepository::get_all();
?>

<div class="wrap">
  <h1 class="wp-heading-inline">
    <?php esc_html_e('Dynamic Forms', 'dynamic-form'); ?>
  </h1>

  <a href="admin.php?page=df-add-form" class="page-title-action">
    <?php esc_html_e('Add New', 'dynamic-form'); ?>
  </a>

  <hr class="wp-header-end">

  <?php if (empty($forms)) : ?>
    <p><?php esc_html_e('No forms found.', 'dynamic-form'); ?></p>
  <?php else : ?>
    <table class="widefat striped">
      <thead>
        <tr>
          <th><?php esc_html_e('ID', 'dynamic-form'); ?></th>
          <th><?php esc_html_e('Name', 'dynamic-form'); ?></th>
          <th><?php esc_html_e('Shortcode', 'dynamic-form'); ?></th>
          <th><?php esc_html_e('Actions', 'dynamic-form'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($forms as $form) : ?>
          <tr>
            <td><?php echo esc_html($form['id']); ?></td>
            <td><?php echo esc_html($form['name']); ?></td>
            <td>
              <code>[dynamic_form id="<?php echo esc_attr($form['id']); ?>"]</code>
            </td>
            <td>
              <a href="admin.php?page=df-builder&form_id=<?php echo esc_attr($form['id']); ?>">
                <?php esc_html_e('Builder', 'dynamic-form'); ?>
              </a> |
              <a href="admin.php?page=df-style-forms&form_id=<?php echo esc_attr($form['id']); ?>">
                <?php esc_html_e('Styles', 'dynamic-form'); ?>
              </a> |
              <a href="#" class="df-delete-form" data-id="<?php echo esc_attr($form['id']); ?>">
                <?php esc_html_e('Delete', 'dynamic-form'); ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
  document.addEventListener('click', function(e) {

    if (!e.target.classList.contains('df-delete-form')) return;

    e.preventDefault();

    if (!confirm('<?php echo esc_js(__('Are you sure you want to delete this form?', 'dynamic-form')); ?>')) {
      return;
    }

    const formId = e.target.dataset.id;

    const data = new FormData();
    data.append('action', 'df_delete_form');
    data.append('form_id', formId);
    data.append('_ajax_nonce', dfAjax.nonce);

    console.log(data.get("_ajax_nonce"));

    fetch(dfAjax.ajax_url, {
        method: 'POST',
        body: data
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          location.reload();
        } else {
          alert(json.data.message);
        }
      });
  });
</script>

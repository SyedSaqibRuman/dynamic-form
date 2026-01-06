<?php
defined('ABSPATH') || exit;
?>

<div class="wrap">
  <h1 class="wp-heading-inline">
    <?php esc_html_e('Add New Form', 'dynamic-form'); ?>
  </h1>

  <hr class="wp-header-end">

  <div id="df-add-form-wrapper" style="max-width:600px;margin-top:20px;">

    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">
            <label for="df-form-name">
              <?php esc_html_e('Form Name', 'dynamic-form'); ?>
            </label>
          </th>
          <td>
            <input
              type="text"
              id="df-form-name"
              class="regular-text"
              placeholder="<?php esc_attr_e('Contact Form', 'dynamic-form'); ?>" />
            <p class="description">
              <?php esc_html_e('Internal name for identifying this form.', 'dynamic-form'); ?>
            </p>
          </td>
        </tr>
      </tbody>
    </table>

    <p>
      <button id="df-create-form-btn" class="button button-primary">
        <?php esc_html_e('Create Form', 'dynamic-form'); ?>
      </button>
      <span id="df-create-form-spinner" class="spinner" style="float:none;"></span>
    </p>

    <div id="df-create-form-response"></div>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    const btn = document.getElementById('df-create-form-btn');
    const input = document.getElementById('df-form-name');
    const responseBox = document.getElementById('df-create-form-response');
    const spinner = document.getElementById('df-create-form-spinner');

    if (!btn || !input) return;

    btn.addEventListener('click', function() {

      const name = input.value.trim();

      responseBox.innerHTML = '';

      if (!name) {
        responseBox.innerHTML =
          '<div class="notice notice-error"><p><?php echo esc_js(__('Form name is required.', 'dynamic-form')); ?></p></div>';
        return;
      }

      spinner.classList.add('is-active');
      btn.disabled = true;

      const data = new FormData();
      data.append('action', 'df_create_form');
      data.append('name', name);
      data.append('_ajax_nonce', dfAjax.nonce);

      fetch(dfAjax.ajax_url, {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(json => {

          spinner.classList.remove('is-active');
          btn.disabled = false;

          if (!json.success) {
            responseBox.innerHTML =
              '<div class="notice notice-error"><p>' + json.data.message + '</p></div>';
            return;
          }

          responseBox.innerHTML =
            '<div class="notice notice-success"><p>' + json.data.message + '</p></div>';

          // Redirect to builder after short delay
          setTimeout(() => {
            window.location.href =
              'admin.php?page=df-builder&form_id=' + json.data.form_id;
          }, 800);
        })
        .catch(() => {
          spinner.classList.remove('is-active');
          btn.disabled = false;

          responseBox.innerHTML =
            '<div class="notice notice-error"><p><?php echo esc_js(__('Unexpected error occurred.', 'dynamic-form')); ?></p></div>';
        });
    });
  });
</script>

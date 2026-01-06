<?php

/**
 * Form Styles Editor
 * Visual style customization with live preview
 *
 * @package DynamicForm
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

// ===================================================
// Security & Capability Check
// ===================================================
if (!current_user_can('manage_options')) {
  wp_die(
    esc_html__('You do not have permission to access this page.', 'dynamic-form'),
    esc_html__('Unauthorized', 'dynamic-form'),
    ['response' => 403]
  );
}

// ===================================================
// Load Data with Error Handling
// ===================================================
$form_id = absint($_GET['form_id'] ?? 0);

// Load all forms
$forms = DF_FormRepository::get_all();
if (!is_array($forms)) {
  wp_die(
    esc_html__('Database error. Unable to load forms.', 'dynamic-form'),
    esc_html__('Database Error', 'dynamic-form'),
    ['response' => 500]
  );
}

// Load selected form
$form = null;
$form_not_found = false;

if ($form_id) {
  $form = DF_FormRepository::get($form_id);
  if (!$form) {
    $form_not_found = true;
    $form_id = 0;
  }
}

// ===================================================
// Default Style Structure (Single Source of Truth)
// ===================================================
$styles = [
  'form'   => ['base' => []],
  'fields' => ['__default' => ['base' => []]],
  'button' => ['base' => []],
];

if ($form) {
  $settings = json_decode($form['settings'] ?? '{}', true);
  if (!empty($settings['styles']) && is_array($settings['styles'])) {
    $styles = wp_parse_args($settings['styles'], $styles);
  }
}

// ===================================================
// NOTE: Style data is localized in DF_AdminMenu::enqueue_assets()
// to ensure it's available before script execution
// ===================================================
?>



<div class="wrap">

  <!-- ===================================================
         Header
    =================================================== -->
  <div class="df-style-form-header">
    <h1><?php esc_html_e('Form Styles', 'dynamic-form'); ?></h1>

    <div class="df-style-form-selector-wrapper">
      <label for="df-form-selector" class="screen-reader-text">
        <?php esc_html_e('Select form to style', 'dynamic-form'); ?>
      </label>
      <select
        id="df-form-selector"
        class="regular-text"
        aria-label="<?php esc_attr_e('Select form to customize styles', 'dynamic-form'); ?>"
        <?php echo empty($forms) ? 'disabled' : ''; ?>>
        <option value="">
          <?php esc_html_e('Select a form…', 'dynamic-form'); ?>
        </option>
        <?php foreach ($forms as $f) : ?>
          <option
            value="<?php echo esc_attr($f['id']); ?>"
            <?php selected($f['id'], $form_id); ?>>
            <?php echo esc_html($f['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- ===================================================
         Error & Empty States
    =================================================== -->
  <?php if (empty($forms)) : ?>
    <div class="notice notice-warning">
      <p>
        <?php
        printf(
          /* translators: %s: Link to add new form */
          esc_html__('No forms found. %s to get started.', 'dynamic-form'),
          '<a href="' . esc_url(admin_url('admin.php?page=df-add-form')) . '">' .
            esc_html__('Create your first form', 'dynamic-form') . '</a>'
        );
        ?>
      </p>
    </div>

  <?php elseif (!$form_id) : ?>
    <div class="notice notice-info">
      <p>
        <strong><?php esc_html_e('Get Started:', 'dynamic-form'); ?></strong>
        <?php esc_html_e('Select a form from the dropdown above to customize its appearance.', 'dynamic-form'); ?>
      </p>
    </div>

  <?php elseif ($form_not_found) : ?>
    <div class="notice notice-error">
      <p>
        <strong><?php esc_html_e('Error:', 'dynamic-form'); ?></strong>
        <?php esc_html_e('The requested form could not be found. It may have been deleted.', 'dynamic-form'); ?>
      </p>
    </div>

  <?php else : ?>

    <!-- ===================================================
             Style Editor Layout
        =================================================== -->
    <div class="df-style-layout">

      <!-- ===================================================
                 LEFT: STYLE CONTROLS
            =================================================== -->
      <div class="df-style-sidebar">

        <div class="df-style-section">

          <h2 id="df-selected-label">
            <?php esc_html_e('Form Styles', 'dynamic-form'); ?>
          </h2>
          <p class="description">
            <?php esc_html_e('Click on form elements in the preview to customize individual fields.', 'dynamic-form'); ?>
          </p>

          <!-- ===================================================
                         SHARED CONTROLS (Form / Field)
                    =================================================== -->
          <table class="form-table" role="presentation">
            <tbody>

              <tr>
                <th scope="row">
                  <label for="df-bg-color">
                    <?php esc_html_e('Background', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="color"
                    id="df-bg-color"
                    aria-label="<?php esc_attr_e('Background color', 'dynamic-form'); ?>"
                    value="<?php echo esc_attr($styles['form']['base']['bg'] ?? '#ffffff'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-text-color">
                    <?php esc_html_e('Text Color', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="color"
                    id="df-text-color"
                    aria-label="<?php esc_attr_e('Text color', 'dynamic-form'); ?>"
                    value="<?php echo esc_attr($styles['form']['base']['color'] ?? '#000000'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-width">
                    <?php esc_html_e('Width (%)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-width"
                    min="1"
                    max="100"
                    aria-label="<?php esc_attr_e('Width in percentage', 'dynamic-form'); ?>">
                  <p class="description">
                    <?php esc_html_e('Leave empty for default width', 'dynamic-form'); ?>
                  </p>
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-height">
                    <?php esc_html_e('Height (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-height"
                    min="0"
                    aria-label="<?php esc_attr_e('Height in pixels', 'dynamic-form'); ?>">
                  <p class="description">
                    <?php esc_html_e('Minimum height for input fields', 'dynamic-form'); ?>
                  </p>
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-font-size">
                    <?php esc_html_e('Font Size (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-font-size"
                    min="8"
                    max="72"
                    aria-label="<?php esc_attr_e('Font size in pixels', 'dynamic-form'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-padding">
                    <?php esc_html_e('Padding (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-padding"
                    min="0"
                    aria-label="<?php esc_attr_e('Padding in pixels', 'dynamic-form'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-margin">
                    <?php esc_html_e('Margin (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-margin"
                    min="0"
                    aria-label="<?php esc_attr_e('Margin in pixels', 'dynamic-form'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-border-color">
                    <?php esc_html_e('Border Color', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="color"
                    id="df-field-border-color"
                    aria-label="<?php esc_attr_e('Border color', 'dynamic-form'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-border-radius">
                    <?php esc_html_e('Border Radius (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-border-radius"
                    min="0"
                    aria-label="<?php esc_attr_e('Border Radius in pixels', 'dynamic-form'); ?>">
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-align">
                    <?php esc_html_e('Alignment', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <select id="df-field-align">
                    <option value="left">
                      <?php esc_html_e('Left', 'dynamic-form'); ?>
                    </option>
                    <option value="center">
                      <?php esc_html_e('Center', 'dynamic-form'); ?>
                    </option>
                    <option value="right">
                      <?php esc_html_e('Right', 'dynamic-form'); ?>
                    </option>
                  </select>
                </td>
              </tr>

              <tr>
                <th scope="row">
                  <label for="df-field-gap">
                    <?php esc_html_e('Field Gap (px)', 'dynamic-form'); ?>
                  </label>
                </th>
                <td>
                  <input
                    type="number"
                    id="df-field-gap"
                    min="0"
                    aria-label="<?php esc_attr_e('Space between fields in pixels', 'dynamic-form'); ?>"
                    value="<?php echo esc_attr($styles['form']['base']['field_gap'] ?? 16); ?>">
                  <p class="description">
                    <?php esc_html_e('Space between form fields', 'dynamic-form'); ?>
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ===================================================
                     ACTIONS
                =================================================== -->
        <div class="df-style-actions">
          <button
            id="df-save-styles"
            class="button button-primary button-large"
            data-form-id="<?php echo esc_attr($form_id); ?>"
            data-nonce="<?php echo esc_attr(wp_create_nonce('df_save_style_' . $form_id)); ?>"
            aria-label="<?php esc_attr_e('Save style changes', 'dynamic-form'); ?>">
            <?php esc_html_e('Save Styles', 'dynamic-form'); ?>
          </button>

          <span
            class="spinner"
            id="df-style-spinner"
            role="status"
            aria-live="polite"></span>

          <span
            id="df-save-status"
            class="df-save-status"
            role="status"
            aria-live="polite"></span>
        </div>

      </div>

      <!-- ===================================================
                 RIGHT: LIVE PREVIEW
            =================================================== -->
      <div class="df-style-preview-panel">
        <div class="df-style-preview-header">
          <h3><?php esc_html_e('Live Preview', 'dynamic-form'); ?></h3>
          <p class="description">
            <?php esc_html_e('Changes appear instantly. Click elements to customize.', 'dynamic-form'); ?>
          </p>
        </div>

        <div
          class="df-style-preview"
          id="df-style-preview"
          role="region"
          aria-label="<?php esc_attr_e('Form style preview', 'dynamic-form'); ?>">
          <?php
          try {
            echo DF_Renderer::render_preview($form);
          } catch (Exception $e) {
            echo '<div class="notice notice-error inline"><p>' .
              esc_html__('Unable to render preview.', 'dynamic-form') .
              '</p></div>';
            error_log('DF_Renderer error: ' . $e->getMessage());
          }
          ?>
        </div>
      </div>

    </div>

  <?php endif; ?>

</div>

<!-- Additional styles for improved UI -->
<style>
  .df-style-form-selector-wrapper {
    display: inline-block;
  }

  .df-style-preview-header {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #dcdcde;
  }

  .df-style-preview-header h3 {
    margin: 0 0 4px 0;
    font-size: 15px;
  }

  .df-style-preview-header .description {
    margin: 0;
    color: #646970;
  }

  .df-save-status {
    margin-left: 12px;
    font-weight: 500;
  }

  .df-save-status.success {
    color: #00a32a;
  }

  .df-save-status.error {
    color: #d63638;
  }

  .df-style-actions .dashicons {
    margin-right: 6px;
    vertical-align: middle;
  }

  .df-style-preview {
    transition: opacity 0.2s ease;
  }

  .df-style-preview.df-preview-updating {
    opacity: 0.6;
  }

  .df-form-selector-loading {
    opacity: 0.6;
    pointer-events: none;
  }
</style>
<?php

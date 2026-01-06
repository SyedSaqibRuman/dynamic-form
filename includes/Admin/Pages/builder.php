<?php

/**
 * Form Builder Page
 * Allows admins to create and edit form fields with drag-and-drop interface
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
$nonce_verified = $form_id && wp_verify_nonce($_GET['_wpnonce'] ?? '', 'df_builder_select');

// Load all forms
$forms = DF_FormRepository::get_all();
if (!is_array($forms)) {
  wp_die(
    esc_html__('Unable to load forms. Please check your database connection.', 'dynamic-form'),
    esc_html__('Database Error', 'dynamic-form'),
    ['response' => 500]
  );
}

// Load selected form
$form = null;
$form_not_found = false;

if ($form_id) {
  $form = DF_FormRepository::get($form_id);

  if (!$form && $nonce_verified) {
    $form_not_found = true;
    $form_id = 0; // Reset to prevent JS errors
  }
}

// Parse fields
$fields = [];
if ($form) {
  $fields = json_decode($form['fields'] ?? '[]', true);
  $fields = is_array($fields) ? $fields : [];
}

// ===================================================
// Localize Data for JavaScript
// ===================================================
wp_localize_script('df-admin', 'dfBuilderData', [
  'formId'    => $form_id,
  'fields'    => $fields,
  'nonce'     => DF_Nonce::create_admin(),
  'selectNonce' => wp_create_nonce('df_builder_select'),
  'i18n'      => [
    'selectForm'        => __('Select a form to start building.', 'dynamic-form'),
    'unsavedChanges'    => __('You have unsaved changes. Are you sure you want to leave?', 'dynamic-form'),
    'deleteConfirm'     => __('Are you sure you want to delete this field?', 'dynamic-form'),
    'formNotFound'      => __('Form not found or has been deleted.', 'dynamic-form'),
    'loadingForm'       => __('Loading form...', 'dynamic-form'),
    'fieldRequired'     => __('This field is required.', 'dynamic-form'),
  ]
]);
?>

<div class="wrap df-builder-page">

  <!-- ===================================================
         Header
    =================================================== -->
  <div class="df-builder-header">
    <h1><?php esc_html_e('Form Builder', 'dynamic-form'); ?></h1>

    <div class="df-builder-actions">
      <!-- Form Selector -->
      <label for="df-form-selector" class="screen-reader-text">
        <?php esc_html_e('Select form to edit', 'dynamic-form'); ?>
      </label>
      <select
        id="df-form-selector"
        class="regular-text"
        aria-label="<?php esc_attr_e('Select form to edit', 'dynamic-form'); ?>"
        <?php echo empty($forms) ? 'disabled' : ''; ?>>
        <option value="">
          <?php esc_html_e('Select form…', 'dynamic-form'); ?>
        </option>
        <?php foreach ($forms as $f) : ?>
          <option
            value="<?php echo esc_attr($f['id']); ?>"
            <?php selected($f['id'], $form_id); ?>>
            <?php echo esc_html($f['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Add Field Button -->
      <button
        id="df-add-field"
        class="button button-primary"
        aria-label="<?php esc_attr_e('Add new field to form', 'dynamic-form'); ?>"
        <?php echo !$form ? 'disabled' : ''; ?>>
        <!-- <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> -->
        <?php esc_html_e('Add Field', 'dynamic-form'); ?>
      </button>
    </div>
  </div>

  <!-- ===================================================
         Error Messages
    =================================================== -->
  <?php if ($form_not_found) : ?>
    <div class="notice notice-error is-dismissible">
      <p>
        <strong><?php esc_html_e('Error:', 'dynamic-form'); ?></strong>
        <?php esc_html_e('The requested form could not be found. It may have been deleted.', 'dynamic-form'); ?>
      </p>
    </div>
  <?php endif; ?>

  <?php if (empty($forms)) : ?>
    <div class="notice notice-warning">
      <p>
        <?php
        printf(
          /* translators: %s: Link to add new form page */
          esc_html__('No forms found. %s to get started.', 'dynamic-form'),
          '<a href="' . esc_url(admin_url('admin.php?page=df-add-form')) . '">' .
            esc_html__('Create your first form', 'dynamic-form') . '</a>'
        );
        ?>
      </p>
    </div>
  <?php endif; ?>

  <!-- ===================================================
         Builder Canvas (Form Selected)
    =================================================== -->
  <?php if ($form) : ?>

    <!-- Fields Container -->
    <div
      id="df-builder"
      class="df-builder-canvas"
      role="list"
      aria-label="<?php esc_attr_e('Form fields', 'dynamic-form'); ?>">
      <!-- Rendered by JavaScript -->
    </div>

    <!-- Empty State -->
    <div
      id="df-empty-state"
      class="df-empty-state"
      style="display: none;"
      role="status">
      <div class="df-empty-state-icon">
        <span class="dashicons dashicons-forms" aria-hidden="true"></span>
      </div>
      <p class="df-empty-state-text">
        <?php esc_html_e('No fields added yet. Click "Add Field" to start building your form.', 'dynamic-form'); ?>
      </p>
    </div>

    <!-- Footer Actions -->
    <div class="df-builder-footer">
      <button
        id="df-save-builder"
        class="button button-primary button-large"
        aria-label="<?php esc_attr_e('Save form changes', 'dynamic-form'); ?>">
        <span class="dashicons dashicons-saved" aria-hidden="true"></span>
        <?php esc_html_e('Save Form', 'dynamic-form'); ?>
      </button>

      <span
        class="spinner"
        id="df-builder-spinner"
        role="status"
        aria-live="polite"
        aria-label="<?php esc_attr_e('Saving...', 'dynamic-form'); ?>"></span>

      <span
        id="df-save-status"
        class="df-save-status"
        role="status"
        aria-live="polite"></span>
    </div>

  <?php else : ?>

    <!-- No Form Selected State -->
    <div class="notice notice-info" role="status">
      <p>
        <?php esc_html_e('Select a form from the dropdown above to start building.', 'dynamic-form'); ?>
      </p>
    </div>

  <?php endif; ?>

</div>

<!-- ===================================================
     CSS for Empty State (could be moved to admin.css)
=================================================== -->
<style>
  .df-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f6f7f7;
    border: 2px dashed #c3c4c7;
    border-radius: 8px;
    margin-top: 20px;
  }

  .df-empty-state-icon .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #a7aaad;
  }

  .df-empty-state-text {
    margin-top: 16px;
    font-size: 15px;
    color: #50575e;
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

  .df-builder-header .dashicons {
    margin-right: 4px;
    vertical-align: middle;
  }

  .df-builder-footer .dashicons {
    margin-right: 6px;
  }

  /* Loading state for selector */
  .df-form-selector-loading {
    opacity: 0.6;
    pointer-events: none;
  }
</style>

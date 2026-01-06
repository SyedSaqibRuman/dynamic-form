/**
 * Dynamic Form — Admin Builder JavaScript (Improved)
 * Handles form building, field management, and state tracking
 */

(function () {
  "use strict";

  /* =====================================================
   * State Management
   * ===================================================== */
  let hasUnsavedChanges = false;
  let isFormLoading = false;

  /* =====================================================
   * Utility Functions
   * ===================================================== */
  const $ = (id) => document.getElementById(id);

  function showNotice(type, message) {
    const notice = document.createElement("div");
    notice.className = `notice notice-${type} is-dismissible`;
    notice.innerHTML = `<p>${message}</p>`;

    const wrap = document.querySelector(".wrap");
    wrap?.prepend(notice);

    // Auto-dismiss after 4 seconds
    setTimeout(() => notice.remove(), 4000);

    // Add dismiss button functionality
    notice.querySelector(".notice-dismiss")?.addEventListener("click", () => {
      notice.remove();
    });
  }

  /* =====================================================
   * Form Selector with State Management
   * ===================================================== */
  function initFormSelector() {
    const selector = $("df-form-selector");
    if (!selector || !window.dfBuilderData) return;

    selector.addEventListener("change", function () {
      const newFormId = this.value;

      // No form selected
      if (!newFormId) return;

      // Check for unsaved changes
      if (hasUnsavedChanges) {
        const confirmed = confirm(dfBuilderData.i18n.unsavedChanges);
        if (!confirmed) {
          // Revert to current form
          this.value = dfBuilderData.formId || "";
          return;
        }
      }

      // Show loading state
      this.classList.add("df-form-selector-loading");
      this.disabled = true;
      isFormLoading = true;

      // Add loading spinner
      const spinner = document.createElement("span");
      spinner.className = "spinner is-active";
      spinner.style.float = "none";
      spinner.style.marginLeft = "8px";
      this.parentElement.appendChild(spinner);

      // Navigate with nonce
      const url = new URL(window.location.href);
      url.searchParams.set("form_id", newFormId);
      url.searchParams.set("_wpnonce", dfBuilderData.selectNonce);
      window.location.href = url.toString();
    });
  }

  /* =====================================================
   * Unsaved Changes Warning
   * ===================================================== */
  function initUnsavedWarning() {
    // Track changes when fields are modified
    document.addEventListener("click", (e) => {
      if (
        e.target.closest(".df-edit") ||
        e.target.closest(".df-delete") ||
        e.target.closest("#df-add-field")
      ) {
        hasUnsavedChanges = true;
      }
    });

    // Warn before leaving page
    window.addEventListener("beforeunload", (e) => {
      if (hasUnsavedChanges && !isFormLoading) {
        e.preventDefault();
        e.returnValue = "";
        return "";
      }
    });

    // Reset after successful save
    const originalSaveHandler = window.handleSaveSuccess;
    window.handleSaveSuccess = function () {
      hasUnsavedChanges = false;
      if (originalSaveHandler) originalSaveHandler();
    };
  }

  /* =====================================================
   * Field Rendering
   * ===================================================== */
  function renderPreview(field) {
    const type = field.fieldType;
    const name = field.fieldName || "";

    switch (type) {
      case "subheading":
        return `<div class="df-subheading-preview">${name}</div>`;

      case "textarea":
        return `<textarea disabled placeholder="${name}"></textarea>`;

      case "select":
        return `
          <select disabled>
            <option>${dfBuilderData.i18n?.selectOption || "Select..."}</option>
            ${(field.options || []).map((o) => `<option>${o}</option>`).join("")}
          </select>
        `;

      case "radio":
        return (field.options || [])
          .map(
            (o) =>
              `<label style="margin-right:12px;">
                <input type="radio" name="${field.id}" disabled> ${o}
              </label>`
          )
          .join("");

      case "checkbox":
        return (field.options || [])
          .map(
            (o) =>
              `<label style="margin-right:12px;">
                <input type="checkbox" disabled> ${o}
              </label>`
          )
          .join("");

      case "phone":
        return `<input type="tel" disabled placeholder="${name}">`;

      case "email":
        return `<input type="email" disabled placeholder="${name}">`;

      default:
        return `<input type="text" disabled placeholder="${name}">`;
    }
  }

  function getFieldTypeLabel(type) {
    const labels = {
      text: "Text",
      email: "Email",
      phone: "Phone",
      textarea: "Textarea",
      select: "Dropdown",
      checkbox: "Checkbox",
      radio: "Radio",
      subheading: "Subheading",
      number: "Number",
    };
    return labels[type] || type;
  }

  function renderBuilder() {
    const wrap = $("df-builder");
    const emptyState = $("df-empty-state");

    if (!wrap || !window.dfBuilderData) return;

    const fields = dfBuilderData.fields || [];

    // Show empty state if no fields
    if (fields.length === 0) {
      wrap.style.display = "none";
      if (emptyState) emptyState.style.display = "block";
      return;
    }

    // Hide empty state and render fields
    if (emptyState) emptyState.style.display = "none";
    wrap.style.display = "grid";
    wrap.innerHTML = "";

    fields.forEach((field, index) => {
      const card = document.createElement("div");
      card.className = "df-field-card";
      card.draggable = true;
      card.dataset.index = index;
      card.setAttribute("role", "listitem");
      card.setAttribute(
        "aria-label",
        `Field ${index + 1}: ${field.fieldName} (${getFieldTypeLabel(field.fieldType)})`
      );

      card.innerHTML = `
        <div class="df-field-header">
          <div>
            <span class="df-field-title">${field.fieldName || "Untitled Field"}</span>
            <span class="df-field-meta">(${getFieldTypeLabel(field.fieldType)})</span>
            ${field.required ? '<span class="df-required" aria-label="Required">*</span>' : ""}
          </div>
          <div class="df-field-actions">
            <a href="#" class="df-edit" data-index="${index}" aria-label="Edit ${field.fieldName}">
              Edit
            </a>
            <a href="#" class="df-delete delete" data-index="${index}" aria-label="Delete ${field.fieldName}">
              Delete
            </a>
          </div>
        </div>

        <div class="df-field-preview">
          ${
            field.fieldType === "checkbox" || field.fieldType === "radio"
              ? `<div class="df-options-row">${renderPreview(field)}</div>`
              : renderPreview(field)
          }
        </div>
      `;

      wrap.appendChild(card);
    });

    initDragAndDrop();
  }

  /* =====================================================
   * Drag & Drop Ordering
   * ===================================================== */
  function initDragAndDrop() {
    let dragIndex = null;

    document.querySelectorAll(".df-field-card").forEach((card) => {
      card.addEventListener("dragstart", function () {
        dragIndex = this.dataset.index;
        this.classList.add("dragging");
        this.setAttribute("aria-grabbed", "true");
      });

      card.addEventListener("dragend", function () {
        this.classList.remove("dragging");
        this.setAttribute("aria-grabbed", "false");
      });

      card.addEventListener("dragover", (e) => {
        e.preventDefault();
        card.classList.add("drag-over");
      });

      card.addEventListener("dragleave", () => {
        card.classList.remove("drag-over");
      });

      card.addEventListener("drop", function () {
        this.classList.remove("drag-over");

        const dropIndex = this.dataset.index;
        if (dragIndex === null || dropIndex === dragIndex) return;

        const moved = dfBuilderData.fields.splice(dragIndex, 1)[0];
        dfBuilderData.fields.splice(dropIndex, 0, moved);

        hasUnsavedChanges = true;
        renderBuilder();

        showNotice("info", "Field order changed. Don't forget to save!");
      });
    });
  }

  /* =====================================================
   * Modal (Add / Edit Field)
   * ===================================================== */
  function openModal(field = null, index = null) {
    closeModal();

    const isEdit = field !== null;
    const modal = document.createElement("div");
    modal.id = "df-modal";
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-modal", "true");
    modal.setAttribute(
      "aria-labelledby",
      "df-modal-title"
    );

    modal.innerHTML = `
      <div class="df-modal-backdrop" aria-hidden="true"></div>

      <div class="df-modal-panel">
        <div class="df-modal-header">
          <h2 id="df-modal-title">${isEdit ? "Edit Field" : "Add Field"}</h2>
        </div>

        <div class="df-modal-body">

          <div class="df-form-row">
            <label for="df-label">
              Label <span class="required" aria-label="required">*</span>
            </label>
            <input
              type="text"
              id="df-label"
              value="${field?.fieldName || ""}"
              required
              aria-required="true">
          </div>

          <div class="df-form-row">
            <label for="df-type">Field Type</label>
            <select id="df-type">
              ${[
                ["text", "Text"],
                ["email", "Email"],
                ["phone", "Phone Number"],
                ["number", "Number"],
                ["textarea", "Textarea"],
                ["select", "Dropdown"],
                ["radio", "Radio Button"],
                ["checkbox", "Checkbox"],
                ["subheading", "Subheading"],
              ]
                .map(
                  ([val, label]) =>
                    `<option value="${val}" ${
                      field?.fieldType === val ? "selected" : ""
                    }>${label}</option>`
                )
                .join("")}
            </select>
          </div>

          <div class="df-form-row" id="df-required-row">
            <label>
              <input
                type="checkbox"
                id="df-required"
                ${field?.required ? "checked" : ""}>
              Required
            </label>
          </div>

          <div class="df-form-row" id="df-options-row" style="display: none;">
            <label for="df-options">
              Options <span>(comma separated)</span>
            </label>
            <input
              type="text"
              id="df-options"
              value="${field?.options?.join(", ") || ""}"
              placeholder="Option 1, Option 2, Option 3">
            <p class="description">Enter options separated by commas</p>
          </div>

        </div>

        <div class="df-modal-footer">
          <button class="button button-primary" id="df-save-field">
            ${isEdit ? "Update Field" : "Add Field"}
          </button>
          <button class="button" id="df-cancel-field">Cancel</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    // Focus first input
    setTimeout(() => $("df-label")?.focus(), 100);

    // Toggle conditional rows
    toggleConditionalRows();
    $("df-type")?.addEventListener("change", toggleConditionalRows);

    // Handle escape key
    modal.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });

    // Handle backdrop click
    modal.querySelector(".df-modal-backdrop")?.addEventListener("click", closeModal);

    // Handle cancel
    $("df-cancel-field")?.addEventListener("click", closeModal);

    // Handle save
    $("df-save-field")?.addEventListener("click", () => saveField(field, index));
  }

  function toggleConditionalRows() {
    const type = $("df-type")?.value;
    const optionsRow = $("df-options-row");
    const requiredRow = $("df-required-row");

    if (optionsRow) {
      optionsRow.style.display = ["select", "checkbox", "radio"].includes(type)
        ? "block"
        : "none";
    }

    if (requiredRow) {
      requiredRow.style.display = type === "subheading" ? "none" : "block";
    }
  }

  function saveField(originalField, index) {
    const labelInput = $("df-label");
    const typeInput = $("df-type");
    const requiredInput = $("df-required");
    const optionsInput = $("df-options");

    const label = labelInput?.value.trim();
    const type = typeInput?.value;

    // Validation
    if (!label) {
      alert(dfBuilderData.i18n.fieldRequired || "Label is required");
      labelInput?.focus();
      return;
    }

    // Parse options
    let options = [];
    if (["select", "checkbox", "radio"].includes(type)) {
      options = (optionsInput?.value || "")
        .split(",")
        .map((o) => o.trim())
        .filter(Boolean);

      if (options.length === 0) {
        alert(`${type} fields require at least one option`);
        optionsInput?.focus();
        return;
      }
    }

    const newField = {
      id: originalField?.id || "fld_" + Date.now(),
      fieldName: label,
      fieldType: type,
      required: requiredInput?.checked || false,
      options: options,
      styles: originalField?.styles || {
        base: {},
        tablet: {},
        mobile: {},
      },
    };

    if (index !== null) {
      dfBuilderData.fields[index] = newField;
    } else {
      dfBuilderData.fields.push(newField);
    }

    hasUnsavedChanges = true;
    closeModal();
    renderBuilder();

    showNotice(
      "success",
      index !== null ? "Field updated successfully" : "Field added successfully"
    );
  }

  function closeModal() {
    $("df-modal")?.remove();
  }

  /* =====================================================
   * Save Form
   * ===================================================== */
  function initSaveHandler() {
    const saveBtn = $("df-save-builder");
    const spinner = $("df-builder-spinner");
    const statusEl = $("df-save-status");

    if (!saveBtn) return;

    saveBtn.addEventListener("click", function (e) {
      e.preventDefault();

      if (!window.dfBuilderData?.formId) {
        showNotice("error", "Invalid form ID");
        return;
      }

      // Disable button
      saveBtn.disabled = true;
      spinner?.classList.add("is-active");
      if (statusEl) {
        statusEl.textContent = "Saving...";
        statusEl.className = "df-save-status";
      }

      const data = new FormData();
      data.append("action", "df_save_form");
      data.append("form_id", dfBuilderData.formId);
      data.append("json", JSON.stringify(dfBuilderData.fields));
      data.append("_ajax_nonce", dfAjax.nonce);
      fetch(dfAjax.ajax_url, {
        method: "POST",
        body: data,
      })
        .then((r) => r.json())
        .then((json) => {
          spinner?.classList.remove("is-active");
          saveBtn.disabled = false;

          if (!json.success) {
            if (statusEl) {
              statusEl.textContent = "❌ " + (json.data?.message || "Save failed");
              statusEl.className = "df-save-status error";
            }
            showNotice("error", json.data?.message || "Save failed");
            return;
          }

          // Success
          hasUnsavedChanges = false;
          if (statusEl) {
            statusEl.textContent = "✓ Saved";
            statusEl.className = "df-save-status success";
          }
          showNotice("success", json.data.message || "Form saved successfully");

          // Clear status after 3 seconds
          setTimeout(() => {
            if (statusEl) statusEl.textContent = "";
          }, 3000);
        })
        .catch(() => {
          spinner?.classList.remove("is-active");
          saveBtn.disabled = false;

          if (statusEl) {
            statusEl.textContent = "❌ Network error";
            statusEl.className = "df-save-status error";
          }
          showNotice("error", "Network error. Please try again.");
        });
    });
  }

  /* =====================================================
   * Event Delegation
   * ===================================================== */
  document.addEventListener("click", (e) => {
    // Add field
    if (e.target.closest("#df-add-field")) {
      e.preventDefault();
      openModal();
    }

    // Edit field
    const editBtn = e.target.closest(".df-edit");
    if (editBtn) {
      e.preventDefault();
      const index = parseInt(editBtn.dataset.index);
      openModal(dfBuilderData.fields[index], index);
    }

    // Delete field
    const deleteBtn = e.target.closest(".df-delete");
    if (deleteBtn) {
      e.preventDefault();
      const index = parseInt(deleteBtn.dataset.index);
      const field = dfBuilderData.fields[index];

      const confirmed = confirm(
        `Are you sure you want to delete "${field.fieldName}"?`
      );

      if (confirmed) {
        dfBuilderData.fields.splice(index, 1);
        hasUnsavedChanges = true;
        renderBuilder();
        showNotice("info", "Field deleted. Don't forget to save!");
      }
    }
  });

  /* =====================================================
   * Initialize on DOM Ready
   * ===================================================== */
  document.addEventListener("DOMContentLoaded", () => {
    if (window.dfBuilderData) {
      renderBuilder();
      initFormSelector();
      initUnsavedWarning();
      initSaveHandler();
    }
  });
})();

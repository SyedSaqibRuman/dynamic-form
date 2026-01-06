"use strict";
document.addEventListener("DOMContentLoaded", () => {

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const form_id = urlParams.get('form_id');

  const fieldTypes = Object.freeze({
    FIELD: "field",
    BUTTON: "button",
    FORM: "form"
  })

  const styleData = {
    form: {
      base: {
        bg: "#ffffff",
        color: "#000000",
      }
    },
    button: {
      base: {
        bg: "#1978c7",
        color: "#ffffff"
      }
    },
    fields: {}
  };
  if (typeof dfAjax === "undefined") {
    console.error("DF Style Form: Required data not loaded");
    return;
  }

  /* ===============================
 * Core DOM Elements
 * =============================== */
  const formEl = document.getElementById("df-form");
  const saveBtn = document.getElementById("df-save-styles");
  const formSelector = document.getElementById("df-form-selector");

  if (saveBtn) {
    saveBtn.addEventListener("click", _ => {

      fetch(dfAjax.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "df_save_style",
          _ajax_nonce: dfAjax.nonce,
          form_id: form_id,
          json: JSON.stringify(styleData)
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Styles saved successfully");
          } else {
            alert("Failed to save styles");
            console.error(data);
          }
        })
        .catch(err => {
          console.error("Save error:", err);
        });
    });
  }

  formSelector.addEventListener("change", _ => {
    const newFormId = formSelector.value;

    if (!newFormId) return;

    window.location.href = `admin.php?page=df-style-forms&form_id=${newFormId}`;

  });

  if (!formEl) return;

  //Styling Inputs
  const bgColorInput = document.getElementById("df-bg-color");
  const textColorInput = document.getElementById("df-text-color");
  const widthInput = document.getElementById("df-field-width");
  const heightInput = document.getElementById("df-field-height");
  const fontSizeInput = document.getElementById("df-field-font-size");
  const paddingInput = document.getElementById("df-field-padding");
  const marginInput = document.getElementById("df-field-margin");
  const borderRadiusInput = document.getElementById("df-field-border-radius");
  const fieldAlignment = document.getElementById("df-field-align");
  const filedGapInput = document.getElementById("df-field-gap");


  if (bgColorInput) {
    bgColorInput.addEventListener('input', (e) => {
      const targetStyle = getStyleTarget();
      targetStyle.bg = bgColorInput.value;
      applyStyle();
    });
  }

  if (textColorInput) {
    textColorInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.color = textColorInput.value;
      applyStyle();
    });
  }

  if (widthInput) {
    widthInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.width = widthInput.value + "%";
      applyStyle();
    });
  }

  if (heightInput) {
    heightInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.height = heightInput.value + "px";
      applyStyle();
    });
  }


  if (fontSizeInput) {
    fontSizeInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.fontSize = fontSizeInput.value + "px";
      applyStyle();
    });
  }

  if (paddingInput) {
    paddingInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.padding = paddingInput.value + "px";
      applyStyle();
    });
  }

  if (marginInput) {
    marginInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.margin = marginInput.value + "px";
      applyStyle();
    });
  }

  if (borderRadiusInput) {
    borderRadiusInput.addEventListener('input', e => {
      const targetStyle = getStyleTarget();
      targetStyle.borderRadius = borderRadiusInput.value + "px";
      applyStyle();
    });
  }

  if (fieldAlignment) {
    fieldAlignment.addEventListener('change', e => {
      const targetStyle = getStyleTarget();
      targetStyle.fieldAlignment = fieldAlignment.value;
      console.log(fieldAlignment.value);
      applyStyle();
    });
  }
  if (filedGapInput) {
    filedGapInput.addEventListener('input', _ => {
      const targetStyle = getStyleTarget();
      targetStyle.fieldGap = filedGapInput.value + "px";
      // console.log(targetStyle);
      applyStyle();
    });
  }
  /* ===============================
   * Selection State
   * =============================== */
  let selection = {
    type: fieldTypes.FORM,
    fieldId: null
  };

  /* ===============================
   * Helpers
   * =============================== */
  function clearSelection() {
    formEl.classList.remove("is-active");

    formEl
      .querySelectorAll(".df-field.is-active, .df-field-submit.is-active")
      .forEach(el => el.classList.remove("is-active"));

    selection.type = null;
    selection.fieldId = null;
  }

  function selectForm() {
    clearSelection();
    selection.type = fieldTypes.FORM;
    formEl.classList.add("is-active");
    syncInputControls();
  }

  function selectField(fieldEl) {
    const fieldId = fieldEl.dataset.fieldId;
    if (!fieldId) return;

    clearSelection();
    selection.type = fieldTypes.FIELD;
    selection.fieldId = fieldId;
    fieldEl.classList.add("is-active");
    syncInputControls();
  }

  function selectButton(buttonEl) {
    clearSelection();
    selection.type = fieldTypes.BUTTON;
    buttonEl.classList.add("is-active");
    syncInputControls();
  }
  ;
  /* ===============================
   * Click Handling
   * =============================== */
  formEl.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();

    const buttonEl = e.target.closest(".df-submit-btn");
    const fieldEl = e.target.closest(".df-field");

    if (
      e.target === formEl ||
      (formEl.contains(e.target) && !fieldEl && !buttonEl)
    ) {
      selectForm();
      return;
    }


    if (buttonEl && formEl.contains(buttonEl)) {
      selectButton(buttonEl.closest(".df-field-submit"));
      return;
    }

    if (fieldEl && formEl.contains(fieldEl)) {
      selectField(fieldEl);
      return;
    }
  });


  function getStyleTarget() {
    if (selection.type === fieldTypes.FIELD && selection.fieldId) {
      styleData.fields[selection.fieldId] ??= { base: {} };
      return styleData.fields[selection.fieldId].base;
    }

    if (selection.type === fieldTypes.BUTTON) {
      styleData.button ??= { base: {} };
      return styleData.button.base;
    }

    // default = form
    styleData.form ??= { base: {} };
    styleData.form.base ??= {};
    return styleData.form.base;
  }

  function syncInputControls() {
    const target = getStyleTarget();

    if (bgColorInput) {
      bgColorInput.value = target.bg || "#ffffff";
    }

    if (textColorInput) {
      textColorInput.value = target.color || "#000000";
    }

    if (widthInput) {
      widthInput.value = parseInt(target.width || "100");
    }

    if (heightInput) {
      heightInput.value = parseInt(target.height || "100");
    }

    if (fontSizeInput) {
      fontSizeInput.value = parseInt(target.fontSize || "14");
    }

    if (paddingInput) {
      paddingInput.value = parseInt(target.padding || "12");
    }

    if (marginInput) {
      marginInput.value = parseInt(target.margin || "12");
    } 17

    if (borderRadiusInput) {
      borderRadiusInput.value = parseInt(target.borderRadius || "4");
    }

    if (fieldAlignment) {
      fieldAlignment.value = target.fieldAlignment || "center";
    }

    if (filedGapInput) {
      filedGapInput.value = parseInt(target.fieldGap || "16");
    }
  }

  function applyStyle() {
    // console.log(formEl);



    if (selection.type === fieldTypes.FORM) {
      formEl.style.setProperty("--df-form-bg", styleData.form.base.bg);
      formEl.style.color = styleData.form.base.color;
      formEl.style.width = styleData.form.base.width;
      formEl.style.setProperty("--df-form-padding", styleData.form.base.padding);
      formEl.style.setProperty("--df-field-gap", styleData.form.base.fieldGap);
      return;
    }

    if (selection.type === fieldTypes.FIELD && selection.fieldId) {
      const fieldEl = formEl.querySelector(`[data-field-id="${selection.fieldId}"]`);

      if (!fieldEl) return;

      const base = styleData.fields[selection.fieldId].base;

      // fieldEl.style.backgroundColor = base.bg;
      fieldEl.style.setProperty("--df-font-size", base.fontSize);
      fieldEl.style.setProperty("--df-field-bg", base.bg);
      fieldEl.style.setProperty("--df-field-font-color", base.color);
      fieldEl.style.setProperty("--df-field-padding", base.padding);
      fieldEl.style.setProperty("--df-field-margin", base.margin);
      fieldEl.style.setProperty("--df-field-height", base.height);
      fieldEl.style.setProperty("--df-field-width", base.width);
      return;
    }

    if (selection.type === fieldTypes.BUTTON) {
      const buttonEl = formEl.querySelector(".df-submit-btn");
      if (!buttonEl) return;

      buttonEl.style.setProperty("--df-button-bg", styleData.button.base.bg);
      buttonEl.style.setProperty("--df-button-border-radius", styleData.button.base.borderRadius);
      buttonEl.style.setProperty("--df-button-color", styleData.button.base.color);
      buttonEl.style.setProperty("--df-button-width", styleData.button.base.width);
      buttonEl.style.setProperty("--df-button-height", styleData.button.base.height);
      buttonEl.style.setProperty("--df-button-padding", styleData.button.base.padding);
      buttonEl.style.setProperty("--df-button-margin", styleData.button.base.margin);
      buttonEl.style.setProperty("--df-button-font-size", styleData.button.base.fontSize);
      formEl.style.setProperty("--df-button-align", styleData.button.base.fieldAlignment);

    }
  }


  selectForm();
});

/**
 * Dynamic Form – Frontend JS
 * Handles:
 * - AJAX form submission
 * - Inline success/error messages
 */

document.addEventListener("DOMContentLoaded", () => {

  console.log("I am HERE");
  if (typeof dfFrontend === "undefined") return;

  document.addEventListener("submit", (e) => {
    const form = e.target.closest(".df-form");
    if (!form) return;

    e.preventDefault();

    const messageBox = document.createElement("div");
    messageBox.className = "df-form-message";
    form.appendChild(messageBox);

    const data = new FormData(form);
    data.append("action", "df_submit_form");

    fetch(dfFrontend.ajax_url, {
      method: "POST",
      body: data,
    })
      .then((r) => r.json())
      .then((json) => {
        messageBox.classList.remove("success", "error");

        if (!json.success) {
          messageBox.classList.add("error");
          messageBox.textContent = json.data.message;
          return;
        }

        messageBox.classList.add("success");
        messageBox.textContent = json.data.message;

        form.reset();
      })
      .catch(() => {
        messageBox.classList.add("error");
        messageBox.textContent = "Something went wrong. Please try again.";
      });
  });
});

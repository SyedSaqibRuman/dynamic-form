(function () {
  "use strict";

  const testBtn = document.getElementById("df-test-email");
  const resultEl = document.getElementById("df-test-email-result");

  if (!testBtn || !resultEl || !dfSettings?.nonce) {
    console.error("Test email elements or nonce not found");
    return;
  }

  testBtn.addEventListener("click", async function () {
    // Show loading state
    const originalText = testBtn.textContent;
    testBtn.disabled = true;
    testBtn.textContent = "Sending...";
    resultEl.textContent = "";
    resultEl.className = "";

    try {
      console.log(ajaxurl);
      const response = await fetch(ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          action: "df_test_email",
          _ajax_nonce: dfSettings.nonce
        })
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const res = await response.json();

      if (res.success) {
        resultEl.textContent = res.data?.message || "Test email sent successfully!";
        resultEl.className = "notice notice-success";
      } else {
        resultEl.textContent = res.data?.message || "Failed to send test email.";
        resultEl.className = "notice notice-error";
      }
    } catch (error) {
      console.error("Test email error:", error);
      resultEl.textContent = `Error: ${error.message}`;
      resultEl.className = "notice notice-error";
    } finally {
      // Reset button
      testBtn.disabled = false;
      testBtn.textContent = originalText;
    }
  });


  const input = document.getElementById("df-cc-email");
  const pills = document.getElementById("df-cc-pills");

  if (!input || !pills) return;

  function renderPills() {
    pills.innerHTML = "";

    const emails = input.value
      .split(";")
      .map(e => e.trim())
      .filter(Boolean);

    emails.forEach((email, index) => {
      const pill = document.createElement("span");
      pill.className = "df-cc-pill";
      pill.textContent = email;

      const remove = document.createElement("button");
      remove.type = "button";
      remove.innerHTML = "×";
      remove.title = "Remove";

      remove.addEventListener("click", () => {
        emails.splice(index, 1);
        input.value = emails.join("; ");
        renderPills();
      });

      pill.appendChild(remove);
      pills.appendChild(pill);
    });
  }

  input.addEventListener("input", renderPills);
  renderPills();
})();

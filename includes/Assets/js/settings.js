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
})();

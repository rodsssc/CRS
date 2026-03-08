/**
 * Admin shared helpers (kept small, easy to support).
 * Depends on Bootstrap 5 + SweetAlert2 + global.js helpers.
 */
(function () {
  if (window.CRS_ADMIN) return;

  function csrf() {
    return (typeof getCsrfToken === "function") ? getCsrfToken() : "";
  }

  async function requestJson(url, { method = "GET", body, headers = {} } = {}) {
    const init = {
      method,
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        ...headers,
      },
    };

    if (body instanceof FormData) {
      init.body = body;
      init.headers["X-CSRF-TOKEN"] = csrf();
    } else if (body !== undefined) {
      init.body = JSON.stringify(body);
      init.headers["Content-Type"] = "application/json";
      init.headers["X-CSRF-TOKEN"] = csrf();
    } else {
      init.headers["X-CSRF-TOKEN"] = csrf();
    }

    const res = await fetch(url, init);
    const data = await res.json().catch(() => ({}));
    return { res, data };
  }

  function loading(title = "Loading...") {
    Swal.fire({
      title,
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
      showConfirmButton: false,
    });
  }

  function toastSuccess(message) {
    return Swal.fire({
      icon: "success",
      title: "Success",
      text: message || "Done.",
      timer: 1400,
      showConfirmButton: false,
    });
  }

  function toastError(message) {
    return Swal.fire({
      icon: "error",
      title: "Error",
      text: message || "Something went wrong.",
      confirmButtonColor: "#111827",
    });
  }

  window.CRS_ADMIN = {
    csrf,
    requestJson,
    loading,
    toastSuccess,
    toastError,
  };
})();


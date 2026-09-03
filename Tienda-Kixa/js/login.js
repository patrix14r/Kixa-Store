const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");
const showRegister = document.getElementById("showRegister");
const showLogin = document.getElementById("showLogin");

showRegister.addEventListener("click", (e) => {
  e.preventDefault();
  loginForm.classList.remove("active");
  registerForm.classList.add("active");
});

showLogin.addEventListener("click", (e) => {
  e.preventDefault();
  registerForm.classList.remove("active");
  loginForm.classList.add("active");
});

// 🔐 Simulación de login
loginForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  if (email === "admin@kixa.com" && password === "12345") {
    alert("Inicio de sesión exitoso ✅");
    window.location.href = "index.html";
  } else {
    alert("Correo o contraseña incorrectos ❌");
  }
});

// 🆕 Simulación de registro
registerForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const name = document.getElementById("registerName").value;
  const email = document.getElementById("registerEmail").value;

  alert(`Cuenta creada para ${name} ✅`);
  registerForm.reset();
  registerForm.classList.remove("active");
  loginForm.classList.add("active");
});

function validatePassword() {
  const pass = document.querySelector('[name="password"]');
  const conf = document.querySelector('[name="confirm"]');

  if (pass.value.length < 6) return show(pass,"Min 6 chars");
  if (!/[A-Z]/.test(pass.value)) return show(pass,"Add uppercase");
  if (!/[a-z]/.test(pass.value)) return show(pass,"Add lowercase");
  if (!/[0-9]/.test(pass.value)) return show(pass,"Add number");
  //if (!/[@$!%*#?&]/.test(pass.value)) return show(pass,"Add special char");
  if (pass.value !== conf.value) return show(conf,"Passwords mismatch");
  return true;
}
function show(input,msg){
  input.style.border="2px solid red";
  input.nextElementSibling.textContent=msg;
  return false;
}
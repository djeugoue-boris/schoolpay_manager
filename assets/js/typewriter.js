const text = "Des formations professionnelles certifiantes, des paiements sécurisés, une plateforme fiable.";
let index = 0;
const target = document.getElementById("typewriter-text");

function typeWriter() {
  if (index < text.length) {
    target.textContent += text.charAt(index);
    index++;
    setTimeout(typeWriter, 45);
  }
}

window.onload = typeWriter;

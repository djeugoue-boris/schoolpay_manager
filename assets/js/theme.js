function setTheme(mode) {
  const htmlEl = document.documentElement;
  htmlEl.setAttribute('data-theme', mode);

  localStorage.setItem('theme', mode);
}

// Restaurer le thème au chargement
window.onload = () => {
  const savedTheme = localStorage.getItem('theme') || 'light';
  setTheme(savedTheme);
};

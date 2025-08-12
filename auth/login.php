<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion - SchoolPay</title>
  <style>
    /* Reset global */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0; padding: 0;
    }

    html, body {
      height: 100%;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(270deg, #6a0dad, #7b1fa2, #8e24aa, #7b1fa2, #6a0dad);
      background-size: 150% 150%;
      animation: gradientViolet 20s ease infinite;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      color: #f0e6ff;
      overflow: hidden;
    }

    @keyframes gradientViolet {
      0% {background-position: 0% 50%;}
      50% {background-position: 100% 50%;}
      100% {background-position: 0% 50%;}
    }

    .container {
      max-width: 900px;
      height: 90vh;
      margin: 5vh auto;
      display: flex;
      border-radius: 24px;
      box-shadow: 0 25px 70px rgba(101, 31, 255, 0.5);
      background: #2e0854;
      overflow: hidden;
      color: #f0e6ff;
      animation: fadeInContainer 1s ease forwards;
    }

    @keyframes fadeInContainer {
      from {opacity: 0; transform: translateY(25px);}
      to {opacity: 1; transform: translateY(0);}
    }

    /* Left side */
    .left {
      flex: 1;
      background: linear-gradient(135deg, #5e0b9d 0%, #3a006b 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 60px 50px;
      text-align: center;
      box-shadow: inset 0 0 60px rgba(255 255 255 / 0.1);
      user-select: none;
      position: relative;
    }
    .left img {
    width: 140px;
    height: 140px;
    margin-bottom: 35px;
    border-radius: 12px; /* moins arrondi que 50% */
    box-shadow: 0 0 25px rgba(255 255 255 / 0.7);
    animation: popInLogo 0.9s ease forwards;
    }

    @keyframes popInLogo {
      from {transform: scale(0.6); opacity: 0;}
      to {transform: scale(1); opacity: 1;}
    }

    .left h1 {
      font-size: 3.8rem;
      font-weight: 900;
      margin-bottom: 24px;
      letter-spacing: 0.09em;
      text-shadow: 0 4px 15px rgba(255 255 255 / 0.3);
      user-select: none;
    }

    .left p {
      font-weight: 400;
      font-size: 1.4rem;
      max-width: 370px;
      line-height: 1.7;
      text-shadow: 0 2px 10px rgba(255 255 255 / 0.25);
      user-select: none;
    }

    /* Right side */
    .right {
      flex: 1;
      background: #fff;
      color: #3a006b;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 55px 45px;
      box-shadow: inset 0 0 40px #b899d9;
      position: relative;
      overflow: hidden;
      border-radius: 0 24px 24px 0;
      animation: slideInRight 1s ease forwards;
    }

    @keyframes slideInRight {
      from {opacity: 0; transform: translateX(50px);}
      to {opacity: 1; transform: translateX(0);}
    }

    form {
      width: 100%;
      max-width: 360px;
    }

    form h2 {
      font-weight: 900;
      font-size: 2.6rem;
      color: #6a0dad;
      margin-bottom: 50px;
      text-align: center;
      letter-spacing: 0.05em;
      user-select: none;
      text-shadow: 0 2px 8px rgba(106, 13, 173, 0.4);
    }

    label {
      display: block;
      font-weight: 700;
      color: #5a0177;
      margin-bottom: 8px;
      font-size: 1.05rem;
      user-select: none;
      letter-spacing: 0.02em;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 16px 22px;
      margin-bottom: 34px;
      font-size: 1.15rem;
      border-radius: 16px;
      border: 2.5px solid #d1b3ff;
      background: #faf5ff;
      transition:
        border-color 0.4s cubic-bezier(0.4, 0, 0.2, 1),
        box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: inset 0 5px 15px #e4d6ff;
      outline-offset: 4px;
      outline-color: transparent;
      outline-style: solid;
      outline-width: 4px;
      font-weight: 600;
      color: #3a006b;
    }

    input[type="text"]::placeholder,
    input[type="password"]::placeholder {
      color: #9e80cc;
      font-style: italic;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: #6a0dad;
      box-shadow: 0 0 16px #aa70ffaa, inset 0 8px 20px #cda7ffcc;
      outline-color: #6a0dad;
      outline-offset: 4px;
    }

    input[type="submit"] {
      width: 100%;
      padding: 18px 0;
      border-radius: 20px;
      font-size: 1.3rem;
      font-weight: 900;
      color: white;
      background-image: linear-gradient(45deg, #7b1fa2, #6a0dad);
      border: none;
      cursor: pointer;
      box-shadow:
        0 12px 28px rgba(106,13,173,0.7),
        inset 0 -4px 8px rgba(255,255,255,0.3);
      transition:
        background-position 0.5s ease,
        box-shadow 0.3s ease,
        transform 0.25s ease;
      background-size: 200% 200%;
      background-position: left center;
      user-select: none;
      text-shadow: 0 1px 4px rgba(0,0,0,0.25);
    }

    input[type="submit"]:hover,
    input[type="submit"]:focus {
      background-position: right center;
      box-shadow:
        0 18px 38px rgba(106,13,173,0.9),
        inset 0 -5px 10px rgba(255,255,255,0.4);
      transform: translateY(-3px);
      outline: none;
    }

    input[type="submit"]:active {
      transform: translateY(0);
      box-shadow:
        0 9px 20px rgba(106,13,173,0.75),
        inset 0 -3px 6px rgba(255,255,255,0.25);
    }

    /* Message d’erreur */
    .error {
      background-color: #fce4ff;
      border: 2px solid #a54edc;
      color: #6a0dad;
      padding: 18px 22px;
      border-radius: 20px;
      margin-bottom: 40px;
      font-weight: 700;
      font-size: 1.15rem;
      text-align: center;
      letter-spacing: 0.03em;
      animation: fadeInError 0.7s ease forwards;
      user-select: none;
      box-shadow: 0 0 15px #a54edc88;
    }

    @keyframes fadeInError {
      from {
        opacity: 0;
        transform: translateY(-15px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Responsive */
    @media (max-width: 720px) {
      .container {
        flex-direction: column;
        max-width: 90vw;
        height: auto;
        margin: 30px auto;
        border-radius: 20px;
      }
      .left, .right {
        padding: 35px 25px;
      }
      .left {
        order: 2;
        text-align: center;
        border-radius: 0 0 20px 20px;
      }
      .right {
        order: 1;
        box-shadow: none;
        border-radius: 20px 20px 0 0;
        color: #3a006b;
      }
    }
  </style>
</head>
<body>
  <div class="container" role="main" aria-label="Page de connexion SchoolPay">
    <div class="left" role="complementary" aria-label="Présentation de SchoolPay">
      <img src="../assets/img/logo.jpg" alt="Logo SchoolPay" />
      <h1>Bienvenue sur SchoolPay</h1>
      <p>Votre plateforme de gestion scolaire simple, sécurisée et rapide.</p>
    </div>

    <div class="right">
      <form method="post" action="login_process.php" autocomplete="off" aria-label="Formulaire de connexion">
        <h2>Connexion</h2>

        <!-- Exemple d'erreur à afficher, décommenter et remplacer le texte -->
        <!-- <div class="error" role="alert">Identifiants invalides, veuillez réessayer.</div> -->

        <label for="identifiant">Nom d'utilisateur</label>
        <input type="text" id="identifiant" name="identifiant" placeholder="Entrez votre nom d'utilisateur" required autofocus />

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required />

        <input type="submit" value="Se connecter" />
      </form>
    </div>
  </div>
</body>
</html>

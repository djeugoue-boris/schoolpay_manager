-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 12 août 2025 à 05:32
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `schoolpay_db_3`
--

-- --------------------------------------------------------

--
-- Structure de la table `annees_scolaires`
--

CREATE TABLE `annees_scolaires` (
  `id` int(11) NOT NULL,
  `libelle` varchar(20) NOT NULL,
  `active` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annees_scolaires`
--

INSERT INTO `annees_scolaires` (`id`, `libelle`, `active`, `date_creation`) VALUES
(1, '2024-2025', 1, '2025-08-05 12:14:48'),
(5, '2023-2024', 0, '2025-08-12 06:35:32');

-- --------------------------------------------------------

--
-- Structure de la table `archives`
--

CREATE TABLE `archives` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `fichier` varchar(500) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `archives_backup`
--

CREATE TABLE `archives_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `id_annee` int(11) NOT NULL,
  `chemin_export` text NOT NULL,
  `date_archivage` datetime DEFAULT current_timestamp(),
  `motif` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `autres_paiements`
--

CREATE TABLE `autres_paiements` (
  `id` int(11) NOT NULL,
  `id_inscription` int(11) NOT NULL,
  `id_annee` int(11) DEFAULT NULL,
  `id_section` int(11) DEFAULT NULL,
  `id_classe` int(11) DEFAULT NULL,
  `objet` varchar(255) NOT NULL,
  `montant_paye` decimal(10,2) NOT NULL,
  `date_paiement` datetime DEFAULT current_timestamp(),
  `observations` text DEFAULT NULL,
  `effectue_par` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `autres_paiements`
--

INSERT INTO `autres_paiements` (`id`, `id_inscription`, `id_annee`, `id_section`, `id_classe`, `objet`, `montant_paye`, `date_paiement`, `observations`, `effectue_par`) VALUES
(1, 11, 1, 2, 5, 'Frais de Penalité', 10000.00, '2025-08-11 09:26:18', 'frais de penalites', NULL),
(3, 12, 1, 2, 5, 'Frais de Ratrapage', 15000.00, '2025-08-11 13:06:05', 'Rattrapage Lipro', NULL),
(4, 7, 1, 1, 2, 'Frais de Ratrapage', 15000.00, '2025-08-11 13:09:44', 'frais additfs', NULL),
(5, 14, 1, 2, 8, 'test', 200000.00, '2025-08-11 18:57:32', '', NULL),
(6, 15, 1, 2, 9, 'FRAIS DE BTS', 40000.00, '2025-08-11 19:24:04', 'YES', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `nom_classe` varchar(100) NOT NULL,
  `frais_scolarite` decimal(10,2) DEFAULT 0.00,
  `nombre_tranches` int(11) DEFAULT 1,
  `id_cycle` int(11) NOT NULL,
  `id_section` int(11) NOT NULL,
  `id_annee` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom_classe`, `frais_scolarite`, `nombre_tranches`, `id_cycle`, `id_section`, `id_annee`) VALUES
(1, 'Form 1', 105000.00, 3, 1, 1, 1),
(2, 'Form 2', 125000.00, 4, 1, 1, 1),
(3, '6 ème', 110000.00, 3, 1, 2, 1),
(4, '5 ème', 130000.00, 5, 1, 2, 1),
(5, '4 ème', 135000.00, 4, 1, 2, 1),
(6, '2nd A4 ESP', 145000.00, 3, 2, 2, 1),
(7, '3eme', 200000.00, 2, 2, 2, 1),
(8, 'Tle', 250000.00, 2, 2, 2, 1),
(9, 'GL1', 370000.00, 5, 12, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cycles`
--

CREATE TABLE `cycles` (
  `id` int(11) NOT NULL,
  `nom_cycle` varchar(100) NOT NULL,
  `created_add` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cycles`
--

INSERT INTO `cycles` (`id`, `nom_cycle`, `created_add`) VALUES
(1, '1 ère Cycle', '2025-08-05 08:14:27'),
(2, '2nd Cycle', '2025-08-05 08:15:22'),
(12, 'BTS', '2025-08-11 13:42:22'),
(13, 'LICENCE', '2025-08-11 13:42:47'),
(14, 'MASTER', '2025-08-11 13:43:06'),
(15, 'Doctorat', '2025-08-11 16:13:08');

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `matricule` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `statut` enum('inscrit','demissionnaire','autres') DEFAULT 'inscrit',
  `date_enregistrement` datetime DEFAULT current_timestamp(),
  `frais_inscription` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bourse` decimal(10,2) DEFAULT 0.00,
  `id_classe` int(11) NOT NULL,
  `id_annee` int(11) NOT NULL,
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `matricule`, `nom`, `prenom`, `date_naissance`, `sexe`, `adresse`, `telephone`, `statut`, `date_enregistrement`, `frais_inscription`, `bourse`, `id_classe`, `id_annee`, `date_inscription`) VALUES
(7, 'IMT000002', 'Asonkeng Aroll', 'Loic', '2000-04-03', 'M', 'souza', '690000005', 'inscrit', '2025-08-07 21:03:40', 10000.00, 0.00, 2, 1, '2025-08-07 21:03:40'),
(9, 'IMT000003', 'Djeutatap zebaze', 'paul', '2000-12-12', 'M', 'LOGBESOU-DOUALA', '658589847', 'inscrit', '2025-08-08 19:34:24', 30000.00, 1000.00, 4, 1, '2025-08-08 19:34:24'),
(10, 'IMT000004', 'Matho Fobass', 'ASta', '1999-02-01', 'F', 'bwadibo', '658589847', 'inscrit', '2025-08-09 16:16:06', 10000.00, 5000.00, 4, 1, '2025-08-09 16:16:06'),
(11, 'IMT000005', 'BEBINE ROLAND', 'ULRICHE', '2002-07-25', 'M', 'MAKEPE MISSOKE', '658589847', 'inscrit', '2025-08-11 08:35:39', 10000.00, 5000.00, 5, 1, '2025-08-11 08:35:39'),
(12, 'IMT000006', 'DJANTOU DJIBAN ', 'PATRICE AIMEE', '2001-02-02', 'M', 'MAKEPE MISSOKE', '658589847', 'inscrit', '2025-08-11 08:39:37', 10000.00, 0.00, 5, 1, '2025-08-11 08:39:37'),
(13, 'IMT000007', 'kouam', 'marchal', '2004-02-11', 'M', 'LOGBESOU-DOUALA', '679164801', 'inscrit', '2025-08-11 18:43:56', 10000.00, 15000.00, 5, 1, '2025-08-11 18:43:56'),
(14, 'IMT000008', 'kouami', 'jacque la morsure', '2003-12-11', 'M', 'bwoadibo', '696025269', 'inscrit', '2025-08-11 18:51:55', 15000.00, 10000.00, 8, 1, '2025-08-11 18:51:55'),
(15, 'IMT000009', 'kAMGA NGUEDJUI ', 'SANDY', '2006-02-21', 'F', 'bwoadibo', '689858216', 'inscrit', '2025-08-11 19:16:04', 30000.00, 50000.00, 9, 1, '2025-08-11 19:16:04');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `id_inscription` int(11) NOT NULL,
  `montant_paye` decimal(10,2) NOT NULL,
  `date_paiement` datetime DEFAULT current_timestamp(),
  `effectue_par` int(11) DEFAULT NULL,
  `mode_paiement` varchar(100) DEFAULT NULL,
  `observation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `id_inscription`, `montant_paye`, `date_paiement`, `effectue_par`, `mode_paiement`, `observation`) VALUES
(15, 9, 95000.00, '2025-08-08 00:00:00', NULL, 'espèces', 'tranche 3'),
(16, 9, 5000.00, '2025-08-08 00:00:00', NULL, 'chèque', 'solde'),
(17, 11, 130000.00, '2025-08-11 00:00:00', NULL, 'espèces', NULL),
(18, 12, 125000.00, '2025-08-11 00:00:00', NULL, 'espèces', NULL),
(19, 10, 116000.00, '2025-08-11 00:00:00', NULL, 'espèces', NULL),
(20, 14, 215000.00, '2025-08-11 00:00:00', NULL, 'espèces', NULL),
(21, 14, 10000.00, '2025-08-11 00:00:00', NULL, 'chèque', NULL),
(22, 13, 25000.00, '2025-08-11 00:00:00', NULL, 'espèces', NULL),
(23, 15, 25000.00, '2025-08-11 00:00:00', NULL, 'chèque', NULL),
(24, 15, 150000.00, '2025-08-11 00:00:00', NULL, 'virement bancaire', 'VOUS N\'AVEZ PAS HONTE');

-- --------------------------------------------------------

--
-- Structure de la table `rapports`
--

CREATE TABLE `rapports` (
  `id` int(11) NOT NULL,
  `type_rapport` varchar(100) NOT NULL,
  `chemin_fichier` text NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `genere_par` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `nom_section` varchar(100) NOT NULL,
  `created_add` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cycle_id` int(11) DEFAULT NULL,
  `id_cycle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sections`
--

INSERT INTO `sections` (`id`, `nom_section`, `created_add`, `cycle_id`, `id_cycle`) VALUES
(1, 'Anglophone', '2025-08-05 05:09:17', NULL, 0),
(2, 'Francophone', '2025-08-05 10:55:12', NULL, 0),
(3, 'Bilingue', '2025-08-05 10:57:31', NULL, 0),
(5, 'Franco', '2025-08-11 07:24:51', NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `statistiques`
--

CREATE TABLE `statistiques` (
  `id` int(11) NOT NULL,
  `id_annee` int(11) NOT NULL,
  `total_eleves` int(11) DEFAULT 0,
  `total_paiements` decimal(10,2) DEFAULT 0.00,
  `total_inscriptions` int(11) DEFAULT 0,
  `date_generation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tranches`
--

CREATE TABLE `tranches` (
  `id` int(11) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tranches`
--

INSERT INTO `tranches` (`id`, `libelle`, `created_at`) VALUES
(1, 'Tranche 1', '2025-08-06 15:56:50'),
(2, 'Tranche 2', '2025-08-06 15:56:50'),
(3, 'Tranche 3', '2025-08-06 15:56:50'),
(4, 'Tranche 4', '2025-08-06 15:56:19');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `identifiant` varchar(100) NOT NULL,
  `mot_de_passe` varchar(100) NOT NULL,
  `role` enum('superadmin','admin','caissier') NOT NULL DEFAULT 'admin',
  `date_creation` datetime DEFAULT current_timestamp(),
  `statut` enum('actif','inactif','suspendu') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `identifiant`, `mot_de_passe`, `role`, `date_creation`, `statut`) VALUES
(1, 'superadmin', 'admin123', 'superadmin', '2025-08-05 04:18:08', 'actif'),
(3, 'admin2', '$2y$10$KktiTI0BKGM/sQmUb7D3oeqLk2miejYKuUce5ywaAR/vCGAhPMkye', 'admin', '2025-08-12 06:07:07', 'actif'),
(4, 'admin1', '$2y$10$MPX/nMMonQHy4REehRojPOvwM0qJJltRI4nMH0EwY2ffiqg17YuFq', 'admin', '2025-08-12 06:18:23', 'actif'),
(5, 'caissier', '$2y$10$aEJV/tFL5ahNuUXVPYVS1uOFU377a42UTKS4sxn8Q4baQarjZENMq', 'caissier', '2025-08-12 07:25:31', 'inactif');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annees_scolaires`
--
ALTER TABLE `annees_scolaires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Index pour la table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_archives_date` (`date_creation`),
  ADD KEY `idx_archives_created_by` (`created_by`);

--
-- Index pour la table `autres_paiements`
--
ALTER TABLE `autres_paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_autres_paiements_inscription` (`id_inscription`),
  ADD KEY `idx_ap_annee` (`id_annee`),
  ADD KEY `idx_ap_section` (`id_section`),
  ADD KEY `idx_ap_classe` (`id_classe`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cycle` (`id_cycle`),
  ADD KEY `id_section` (`id_section`),
  ADD KEY `id_annee` (`id_annee`);

--
-- Index pour la table `cycles`
--
ALTER TABLE `cycles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_cycle` (`nom_cycle`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD KEY `matricule_2` (`matricule`),
  ADD KEY `id_classe` (`id_classe`),
  ADD KEY `id_annee` (`id_annee`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_inscription` (`id_inscription`),
  ADD KEY `effectue_par` (`effectue_par`);

--
-- Index pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `genere_par` (`genere_par`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_section` (`nom_section`);

--
-- Index pour la table `statistiques`
--
ALTER TABLE `statistiques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_annee` (`id_annee`);

--
-- Index pour la table `tranches`
--
ALTER TABLE `tranches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifiant` (`identifiant`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annees_scolaires`
--
ALTER TABLE `annees_scolaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `archives`
--
ALTER TABLE `archives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `autres_paiements`
--
ALTER TABLE `autres_paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `cycles`
--
ALTER TABLE `cycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `rapports`
--
ALTER TABLE `rapports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `statistiques`
--
ALTER TABLE `statistiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tranches`
--
ALTER TABLE `tranches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `autres_paiements`
--
ALTER TABLE `autres_paiements`
  ADD CONSTRAINT `fk_autres_paiements_annee` FOREIGN KEY (`id_annee`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_autres_paiements_classe` FOREIGN KEY (`id_classe`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_autres_paiements_inscription` FOREIGN KEY (`id_inscription`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_autres_paiements_section` FOREIGN KEY (`id_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`id_cycle`) REFERENCES `cycles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`id_section`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`id_annee`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_inscription_annee` FOREIGN KEY (`id_annee`) REFERENCES `annees_scolaires` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscription_classe` FOREIGN KEY (`id_classe`) REFERENCES `classes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`id_classe`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`id_annee`) REFERENCES `annees_scolaires` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`id_inscription`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paiements_ibfk_3` FOREIGN KEY (`effectue_par`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `rapports`
--
ALTER TABLE `rapports`
  ADD CONSTRAINT `rapports_ibfk_1` FOREIGN KEY (`genere_par`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `statistiques`
--
ALTER TABLE `statistiques`
  ADD CONSTRAINT `statistiques_ibfk_1` FOREIGN KEY (`id_annee`) REFERENCES `annees_scolaires` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

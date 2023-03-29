-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 29 mars 2023 à 11:42
-- Version du serveur : 10.4.24-MariaDB
-- Version de PHP : 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `IdArticle` int(11) NOT NULL,
  `auteur` varchar(50) NOT NULL,
  `contenu` varchar(500) NOT NULL,
  `datePublication` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`IdArticle`, `auteur`, `contenu`, `datePublication`) VALUES
(10, 'Kevin', ' Coucou les copinous, c\'est Kevinou', '2023-03-22 10:26:51'),
(11, 'Loic', ' Can I get some burger ?', '2023-03-22 10:27:24'),
(12, 'Loic', 'I love the taste of the chicken legs', '2023-03-23 08:13:43');

-- --------------------------------------------------------

--
-- Structure de la table `evaluer`
--

CREATE TABLE `evaluer` (
  `evaluation` int(11) DEFAULT NULL,
  `nom` varchar(50) NOT NULL,
  `IdArticle` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `evaluer`
--

INSERT INTO `evaluer` (`evaluation`, `nom`, `IdArticle`) VALUES
(1, 'Kevin', 10),
(-1, 'Loic', 10);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `nom` varchar(50) NOT NULL,
  `motDePasse` varchar(65) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`nom`, `motDePasse`, `role`) VALUES
('Jules', '$2y$10$.zM2W3.J4ueOBH89EK.nW.R7yLoHo8qdBUC9ZPnLNEYEA44e7V3lG', 'moderator'),
('Kevin', '$2y$10$nGWBBezME13APMNgerFk2ODlR0Q12byI5gjawOpqVwMNa80WqDKsi', 'publisher'),
('Loic', '$2y$10$b8Tc6U/8wk0SZIimFIFR9eL10Q/I7K3Udt41M27a2FlgIlyzi7ydm', 'publisher');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`IdArticle`);

--
-- Index pour la table `evaluer`
--
ALTER TABLE `evaluer`
  ADD PRIMARY KEY (`nom`,`IdArticle`),
  ADD KEY `idArticle` (`IdArticle`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`nom`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `IdArticle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `evaluer`
--
ALTER TABLE `evaluer`
  ADD CONSTRAINT `evaluer_ibfk_1` FOREIGN KEY (`nom`) REFERENCES `utilisateur` (`nom`),
  ADD CONSTRAINT `evaluer_ibfk_2` FOREIGN KEY (`idArticle`) REFERENCES `article` (`IdArticle`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

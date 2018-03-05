-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 06 fév. 2018 à 13:54
-- Version du serveur :  10.2.12-MariaDB
-- Version de PHP :  7.2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données :  `intranet`
--

-- --------------------------------------------------------

--
-- Structure de la table `ServerApps`
--

CREATE TABLE `ServerApps` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `URI` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ServerAssetTypes`
--

CREATE TABLE `ServerAssetTypes` (
  `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Servers`
--

CREATE TABLE `Servers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hostname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastIPLookup` datetime NOT NULL,
  `order` int(11) NOT NULL,
  `apps` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `ServersAssets`
--

CREATE TABLE `ServersAssets` (
  `id` int(11) NOT NULL,
  `typeId` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serverId` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `URL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `SidebarCategories`
--

CREATE TABLE `SidebarCategories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `SidebarEntries`
--

CREATE TABLE `SidebarEntries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `URL` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `categoryId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `photoUrl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `googleAccessToken` longtext COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '(DC2Type:json)',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `UsersLoginHistory`
--

CREATE TABLE `UsersLoginHistory` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `ServerApps`
--
ALTER TABLE `ServerApps`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ServerAssetTypes`
--
ALTER TABLE `ServerAssetTypes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `Servers`
--
ALTER TABLE `Servers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `ServersAssets`
--
ALTER TABLE `ServersAssets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BC6259639EE279FF` (`serverId`),
  ADD KEY `IDX_BC6259639BF49490` (`typeId`);

--
-- Index pour la table `SidebarCategories`
--
ALTER TABLE `SidebarCategories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `SidebarEntries`
--
ALTER TABLE `SidebarEntries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_636A85A9C370B71` (`categoryId`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `UsersLoginHistory`
--
ALTER TABLE `UsersLoginHistory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_79CAEEC064B64DCC` (`userId`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `ServerApps`
--
ALTER TABLE `ServerApps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `Servers`
--
ALTER TABLE `Servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `ServersAssets`
--
ALTER TABLE `ServersAssets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `SidebarCategories`
--
ALTER TABLE `SidebarCategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `SidebarEntries`
--
ALTER TABLE `SidebarEntries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `UsersLoginHistory`
--
ALTER TABLE `UsersLoginHistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `ServersAssets`
--
ALTER TABLE `ServersAssets`
  ADD CONSTRAINT `FK_BC6259639BF49490` FOREIGN KEY (`typeId`) REFERENCES `ServerAssetTypes` (`id`),
  ADD CONSTRAINT `FK_BC6259639EE279FF` FOREIGN KEY (`serverId`) REFERENCES `Servers` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `SidebarEntries`
--
ALTER TABLE `SidebarEntries`
  ADD CONSTRAINT `FK_636A85A9C370B71` FOREIGN KEY (`categoryId`) REFERENCES `SidebarCategories` (`ID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `UsersLoginHistory`
--
ALTER TABLE `UsersLoginHistory`
  ADD CONSTRAINT `FK_79CAEEC064B64DCC` FOREIGN KEY (`userId`) REFERENCES `Users` (`id`);
COMMIT;

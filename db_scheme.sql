SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `coinimp_cache` (
  `uid` int(11) NOT NULL,
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `balance` bigint(20) NOT NULL,
  `need_update` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ip_stats` (
  `uid` int(11) NOT NULL,
  `ip` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `payouts` (
  `uid` int(11) NOT NULL,
  `address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `txid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent` text COLLATE utf8_unicode_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `stats` (
  `uid` int(11) NOT NULL,
  `id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `hashes` bigint(20) NOT NULL DEFAULT '0',
  `ref_uid` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `coinimp_cache`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `need_update` (`need_update`);

ALTER TABLE `ip_stats`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `ip` (`ip`);

ALTER TABLE `payouts`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `timestamp` (`timestamp`);

ALTER TABLE `stats`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `address` (`address`) USING BTREE,
  ADD KEY `ref_uid` (`ref_uid`);


ALTER TABLE `coinimp_cache`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ip_stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `payouts`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `stats`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

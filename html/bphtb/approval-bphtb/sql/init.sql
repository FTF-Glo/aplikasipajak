CREATE TABLE `sessions`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(191) NULL,
  `uid` varchar(191) NULL,
  `expired_date` datetime NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_ssb` int(11) NOT NULL,
  `serial_number` varchar(191) NOT NULL,
  `uid` varchar(191) NOT NULL,
  `type` varchar(191) NOT NULL,
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
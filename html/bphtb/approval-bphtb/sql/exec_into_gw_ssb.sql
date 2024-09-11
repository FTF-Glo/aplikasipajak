ALTER TABLE `ssb` 
ADD COLUMN `approval_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=belum disetujui, 1=disetujui, 2=ditolak',
ADD COLUMN `approval_msg` text NULL COMMENT 'alasan jika di tolak',
ADD COLUMN `approval_qr_text` text NULL;
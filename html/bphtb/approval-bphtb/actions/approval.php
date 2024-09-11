<?php

class approval
{

    public static function approve($id_ssb, $uid = '')
    {
        $ssbData = db::getInstance()
            ->connection('gw_ssb')
            ->where('approval_status', 1, '<>')
            ->where('id_ssb', $id_ssb)
            ->getOne('ssb');

        if ($ssbData) {
            $serialNumber = self::generateSerial();
            $qrText = self::getQRText($ssbData['payment_code'], $serialNumber);
            // update ssb
            $updateSSB = db::getInstance()
                ->connection('gw_ssb')
                ->where('id_ssb', $id_ssb)
                ->update('ssb', array(
                    'approval_status' => 1,
                    'approval_qr_text' => $qrText,
                    'approval_msg' => ''
                ));
            // insert to log
            $logId = db::getInstance()
                ->connection('default')
                ->insert('logs', array(
                    'id_ssb' => $id_ssb,
                    'serial_number' => $serialNumber,
                    'type' => 'approve',
                    'uid' => $uid
                ));
            if (!$updateSSB) {
                set_flash('Terjadi kesalahan ketika melakukan persetujuan', 'danger');
                db::getInstance()
                    ->connection('default')
                    ->where('id', $logId)
                    ->delete('logs');
            }
            set_flash('Laporan berhasil disetujui', 'success');
        }
    }

    public static function reject($id_ssb, $message, $uid = '')
    {
        // update ssb
        $updateSSB = db::getInstance()
            ->connection('gw_ssb')
            ->where('id_ssb', $id_ssb)
            ->update('ssb', array(
                'approval_status' => 2,
                'approval_msg' => $message
            ));
        if (!$updateSSB) {
            set_flash('Terjadi kesalahan ketika melakukan persetujuan', 'danger');
        }
        // insert to log
        $logId = db::getInstance()
            ->connection('default')
            ->insert('logs', array(
                'id_ssb' => $id_ssb,
                'serial_number' => '',
                'type' => 'reject',
                'uid' => $uid
            ));
        set_flash('Laporan berhasil ditolak', 'success');
    }

    public static function changeStatus($id_ssb, $uid = '')
    {
        $ssbData = db::getInstance()
            ->connection('gw_ssb')
            ->where('approval_status', 1, '<>')
            ->where('id_ssb', $id_ssb)
            ->getOne('ssb');

        // * perubahan fungsi, changestatus artinya merubah status menjadi semula. (0)

        $ssbUpdateData = array(
            'approval_status' => 0,
            // 'approval_msg' => ''
        );

        // ketika data ditolak, lalu diubah. belum pernah terjadi persetujuan
        // if ($ssbData && !$ssbData['approval_qr_text']) {
        //     $serialNumber = self::generateSerial();
        //     $qrText = self::getQRText($ssbData['payment_code'], $serialNumber);
        //     $ssbUpdateData = array(
        //         'approval_status' => 1,
        //         'approval_msg' => '',
        //         'approval_qr_text' => $qrText
        //     );
        //     // insert to log if not exists as approve
        //     $logId = db::getInstance()
        //         ->connection('default')
        //         ->insert('logs', array(
        //             'id_ssb' => $id_ssb,
        //             'serial_number' => $serialNumber,
        //             'type' => 'approve',
        //             'uid' => $uid
        //         ));
        // }

        // update ssb
        $updateSSB = db::getInstance()
            ->connection('gw_ssb')
            ->where('id_ssb', $id_ssb)
            ->update('ssb', $ssbUpdateData);
        if (!$updateSSB) {
            set_flash('Terjadi kesalahan ketika melakukan persetujuan', 'danger');
        }
        // insert to log
        $logId = db::getInstance()
            ->connection('default')
            ->insert('logs', array(
                'id_ssb' => $id_ssb,
                'serial_number' => '',
                'type' => 'change status',
                'uid' => $uid
            ));
        set_flash('Laporan berhasil diubah', 'success');
    }

    public static function generateSerial()
    {
        $lastSerial = 0;
        $datePrefix = date('ym');
        $lastSerialData = db::getInstance()
            ->connection('default')
            ->orderBy('id', 'desc')
            ->where('serial_number', $datePrefix . '%', 'LIKE')
            ->where('type', 'approve')
            ->getOne('logs');
        if ($lastSerialData) {
            $lastSerial = $lastSerialData['serial_number'];
        }

        $numberOnly = (int) str_replace($datePrefix, '', $lastSerial);
        $zeroBeforeSerial = (config::get('serial_length'));
        $newNumber = str_pad(($numberOnly + 1), $zeroBeforeSerial, 0, STR_PAD_LEFT);
        return $datePrefix . $newNumber;
    }

    public static function getQRText($paymentCode, $serialNumber)
    {
        $nipPengesah = db::getInstance()
            ->connection('sw_ssb')
            ->where(config::get('config_key_key'), config::get('nip_pengesah_key'))
            ->getOne(config::get('table_config'));
        $namaPengesah = db::getInstance()
            ->connection('sw_ssb')
            ->where(config::get('config_key_key'), config::get('nama_pengesah_key'))
            ->getOne(config::get('table_config'));

        $template = 'PENGESAH: ' . $namaPengesah[config::get('config_value_key')] . QR_BR;
        $template .= 'NIP: ' . $nipPengesah[config::get('config_value_key')] . QR_BR;
        $template .= 'KODE BAYAR: ' . $paymentCode . QR_BR;
        $template .= 'SERIAL: ' . $serialNumber . QR_BR;
        $template .= 'TGL: ' . now();

        return $template;
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $approvalType = inputPost('type');
    $idSSB = inputPost('id_ssb');
    $message = inputPost('msg');
    do {
        if ($approvalType == 3) {
            approval::changeStatus($idSSB, $isUserLoggedIn['uid']);
            break;
        }
        if ($approvalType == 2) {
            approval::reject($idSSB, $message, $isUserLoggedIn['uid']);
            break;
        }
        if ($approvalType == 1) {
            approval::approve($idSSB, $isUserLoggedIn['uid']);
            break;
        }
    } while (true);
}

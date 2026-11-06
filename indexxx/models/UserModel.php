<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel {
    
    public function findByEmail(string $email): ?array {
        $sql = "SELECT nd.*, 
                       kh.ma_kh, kh.dia_chi, kh.ngay_dang_ky,
                       nv.ma_nv, nv.chuc_vu, nv.luong_co_ban, nv.ngay_vao_lam
                FROM nguoi_dung nd
                LEFT JOIN khach_hang kh ON nd.ma_nd = kh.ma_nd
                LEFT JOIN nhan_vien nv ON nd.ma_nd = nv.ma_nd
                WHERE nd.email = ? AND nd.trang_thai = 'HoatDong'
                LIMIT 1";
        return $this->fetchOne($sql, [$email]);
    }

    public function findByPhone(string $phone): ?array {
        $sql = "SELECT * FROM nguoi_dung WHERE sdt = ? LIMIT 1";
        return $this->fetchOne($sql, [$phone]);
    }

    public function findById(int $id): ?array {
        $sql = "SELECT nd.*, 
                       kh.ma_kh, kh.dia_chi, kh.ngay_dang_ky,
                       nv.ma_nv, nv.chuc_vu, nv.luong_co_ban, nv.ngay_vao_lam
                FROM nguoi_dung nd
                LEFT JOIN khach_hang kh ON nd.ma_nd = kh.ma_nd
                LEFT JOIN nhan_vien nv ON nd.ma_nd = nv.ma_nd
                WHERE nd.ma_nd = ?
                LIMIT 1";
        return $this->fetchOne($sql, [$id]);
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        // Kiểm tra mật khẩu (hỗ trợ password_verify và so sánh trực tiếp / md5)
        $valid = false;
        if (password_verify($password, $user['mat_khau'])) {
            $valid = true;
        } elseif ($password === $user['mat_khau'] || md5($password) === $user['mat_khau']) {
            $valid = true;
            // Tự động nâng cấp hash mật khẩu
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $this->execute("UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nd = ?", [$newHash, $user['ma_nd']]);
        }

        return $valid ? $user : null;
    }

    public function createCustomer(string $name, string $phone, string $email, string $password, string $address = ''): int {
        $this->beginTransaction();
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sqlNd = "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, sdt, vai_tro, trang_thai) 
                      VALUES (?, ?, ?, ?, 'KhachHang', 'HoatDong')";
            $this->execute($sqlNd, [$name, $email, $hashedPassword, $phone]);
            $maNd = (int)$this->lastInsertId();

            $sqlKh = "INSERT INTO khach_hang (ma_nd, dia_chi, ngay_dang_ky) 
                      VALUES (?, ?, CURDATE())";
            $this->execute($sqlKh, [$maNd, $address]);

            $this->commit();
            return $maNd;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function createStaff(string $name, string $phone, string $email, string $password, string $role, string $position, float $salary): int {
        $this->beginTransaction();
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sqlNd = "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, sdt, vai_tro, trang_thai) 
                      VALUES (?, ?, ?, ?, ?, 'HoatDong')";
            $this->execute($sqlNd, [$name, $email, $hashedPassword, $phone, $role]);
            $maNd = (int)$this->lastInsertId();

            $sqlNv = "INSERT INTO nhan_vien (ma_nd, chuc_vu, luong_co_ban, ngay_vao_lam) 
                      VALUES (?, ?, ?, CURDATE())";
            $this->execute($sqlNv, [$maNd, $position, $salary]);

            $this->commit();
            return $maNd;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function updateProfile(int $maNd, string $name, string $phone, string $email, ?string $address = null): bool {
        $this->beginTransaction();
        try {
            $sqlNd = "UPDATE nguoi_dung SET ho_ten = ?, sdt = ?, email = ? WHERE ma_nd = ?";
            $this->execute($sqlNd, [$name, $phone, $email, $maNd]);

            if ($address !== null) {
                $sqlKh = "UPDATE khach_hang SET dia_chi = ? WHERE ma_nd = ?";
                $this->execute($sqlKh, [$address, $maNd]);
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function changePassword(int $maNd, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->execute("UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nd = ?", [$hash, $maNd]);
    }

    public function getAllStaff(): array {
        $sql = "SELECT nd.ma_nd, nd.ho_ten, nd.email, nd.sdt, nd.vai_tro, nd.trang_thai,
                       nv.ma_nv, nv.chuc_vu, nv.luong_co_ban, nv.ngay_vao_lam
                FROM nguoi_dung nd
                JOIN nhan_vien nv ON nd.ma_nd = nv.ma_nd
                ORDER BY nd.ma_nd DESC";
        return $this->fetchAll($sql);
    }
}


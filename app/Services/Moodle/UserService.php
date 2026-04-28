<?php

namespace App\Services\Moodle;

use Illuminate\Support\Facades\DB;

class UserService
{
    public static function getUsers(): array
    {
        return DB::select("
            SELECT * FROM mdl_user
            WHERE deleted = 0
            AND username != 'guest'
            ORDER BY firstname ASC
        ");
    }

    public static function getAllUsers(): array
    {
        return self::getUsers();
    }

    public static function getUser(int $id): ?object
    {
        $results = DB::select("
            SELECT * FROM mdl_user WHERE id = ? AND deleted = 0
        ", [$id]);
        return $results[0] ?? null;
    }

    public static function getUserByEmail(string $email): ?object
    {
        $results = DB::select("
            SELECT * FROM mdl_user WHERE email = ? AND deleted = 0
        ", [$email]);
        return $results[0] ?? null;
    }

    public static function getUserByUsername(string $username): ?object
    {
        $results = DB::select("
            SELECT * FROM mdl_user WHERE username = ? AND deleted = 0
        ", [$username]);
        return $results[0] ?? null;
    }

    public static function enrolUserWithTime(
        int $userid,
        int $courseid,
        int $timestart = 0,
        int $timeend = 0,
        int $roleid = 5
    ): bool {
        $enrol = DB::selectOne("
            SELECT * FROM mdl_enrol
            WHERE courseid = ? AND enrol = 'manual'
        ", [$courseid]);

        if (!$enrol) {
            if (getenv('MOODLE_WRITE_ENABLED') === 'true') {
                DB::insert("
                    INSERT INTO mdl_enrol (enrol, status, courseid, roleid, sortorder, timecreated, timemodified)
                    VALUES ('manual', 0, ?, 5, 0, ?, ?)
                ", [$courseid, time(), time()]);

                $enrol = DB::selectOne("
                    SELECT * FROM mdl_enrol
                    WHERE courseid = ? AND enrol = 'manual'
                ", [$courseid]);
            } else {
                return false;
            }
        }

        if (!$enrol) {
            return false;
        }

        $existing = DB::selectOne("
            SELECT * FROM mdl_user_enrolments
            WHERE userid = ? AND enrolid = ?
        ", [$userid, $enrol->id]);

        if ($existing) {
            return true;
        }

        if (getenv('MOODLE_WRITE_ENABLED') !== 'true') {
            return true;
        }

        DB::insert("
            INSERT INTO mdl_user_enrolments (userid, enrolid, status, timestart, timeend, timecreated, timemodified)
            VALUES (?, ?, 0, ?, ?, ?, ?)
        ", [
            $userid,
            $enrol->id,
            $timestart,
            $timeend,
            time(),
            time()
        ]);
        return true;
    }

    public static function createUser(array $data): int
    {
        $password = '';
        if (isset($data['password']) && !empty($data['password'])) {
            $password = $data['password'];
            unset($data['password']);
        }

        $time = time();
        $username = $data['username'] ?? '';
        $firstname = $data['firstname'] ?? '';
        $lastname = $data['lastname'] ?? '';
        $email = $data['email'] ?? '';

        $passwordHash = '';
        if ($password) {
            $passwordHash = self::hashPassword($password);
        }

        DB::insert("
            INSERT INTO mdl_user
            (username, password, firstname, lastname, email, auth, confirmed, deleted, suspended, mnethostid, policyagreed, timecreated, timemodified)
            VALUES (?, ?, ?, ?, ?, 'manual', 1, 0, 0, 1, 0, ?, ?)
        ", [
            $username,
            $passwordHash,
            $firstname,
            $lastname,
            $email,
            $time,
            $time
        ]);

        $result = DB::select('SELECT LAST_INSERT_ID() as id');
        return $result[0]->id;
    }

    public static function hashPassword(string $password): string
    {
        $rounds = 10000;
        $randombytes = random_bytes(16);
        $salt = substr(strtr(base64_encode($randombytes), '+', '.'), 0, 16);

        return crypt($password, implode('$', ['', '6', "rounds={$rounds}", $salt, '']));
    }

    public static function updateUser(int $id, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = self::hashPassword($data['password']);
        }

        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            $sets[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $id;

        DB::update("
            UPDATE mdl_user
            SET " . implode(', ', $sets) . "
            WHERE id = ?
        ", $values);

        return true;
    }

    public static function deleteUser(int $id): bool
    {
        DB::update("
            UPDATE mdl_user
            SET deleted = 1, timemodified = ?
            WHERE id = ?
        ", [time(), $id]);

        return true;
    }
}

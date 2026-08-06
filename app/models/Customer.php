<?php
/**
 * XPOSED — Customer model.
 */

class Customer
{
    public static function findOrCreate(string $email, string $name = ''): int
    {
        $db = db();
        $st = $db->prepare('SELECT id FROM customers WHERE email = ?');
        $st->execute([$email]);
        $row = $st->fetch();
        if ($row) {
            if ($name !== '' && trim($name) !== '') {
                $db->prepare('UPDATE customers SET name = ? WHERE id = ?')->execute([
                    $name, $row['id'],
                ]);
            }
            return (int)$row['id'];
        }
        $st = $db->prepare('INSERT INTO customers (email, name) VALUES (?, ?)');
        $st->execute([$email, $name]);
        return (int)$db->lastInsertId();
    }
}

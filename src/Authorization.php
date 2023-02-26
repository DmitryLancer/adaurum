<?php


namespace Post;


use PDO;

class Authorization
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function register(array $data): bool
    {
        if (empty($data['username'])) {
            throw new AuthorizationException('The username should be empty');
        }
        if (empty($data['email'])) {
            throw new AuthorizationException('The email should be empty');
        }
        if (empty($data['password'])) {
            throw new AuthorizationException('The password should be empty');
        }
        if ($data['password'] !== $data['confirm_password']) {
            throw new AuthorizationException('The Password and Confirm password should match!');
        }

        $statement = $this->connection->prepare(
            'SELECT * FROM user WHERE email = :email'
        );
        $statement->execute([
            'email' => $data['email']
        ]);

        $user = $statement->fetch();
        if (!empty($user)) {
            throw new AuthorizationException('Пользователь с такой почтой уже зарегистрирован');
        }

        $statement = $this->connection->prepare(
            'SELECT * FROM user WHERE username = :username'
        );
        $statement->execute([
            'username' => $data['username']
        ]);

        $user = $statement->fetch();
        if (!empty($user)) {
            throw new AuthorizationException('Пользователь с таким логином уже существует');
        }

        $statement = $this->connection->prepare(
            'INSERT INTO user (email, username, password) VALUES (:email, :username, :password)'
        );
        $statement->execute([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
        ]);


        return true;
    }

}
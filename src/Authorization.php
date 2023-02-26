<?php


namespace Post;


use PDO;

class Authorization
{
    private PDO $connection;
    private Session $session;

    public function __construct(PDO $connection, Session $session)
    {
        $this->connection = $connection;
        $this->session = $session;
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

    public function login(string $email, $password): bool
    {
        if (empty($email)) {
            throw new AuthorizationException('The email should be empty');
        }
        if (empty($password)) {
            throw new AuthorizationException('The password should be empty');
        }

        $statement = $this->connection->prepare(
            'SELECT * FROM user WHERE email = :email'
        );
        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();
        if (empty($user)) {
            throw new AuthorizationException('User with such email not found');
        }

        if (password_verify($password, $user['password'])) {
            $this->session->setData('user', [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ]);
            return true;
        }

        throw new AuthorizationException('Incorrect email or password');
    }

}
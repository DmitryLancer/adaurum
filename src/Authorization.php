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
            throw new AuthorizationException('Поле "Имя пользователя" не заполнено');
        }
        if (empty($data['email'])) {
            throw new AuthorizationException('Поле "Email" не заполнено');
        }
        if (empty($data['password'])) {
            throw new AuthorizationException('Поле "Пароль" не заполнено');
        }
        if ($data['password'] !== $data['confirm_password']) {
            throw new AuthorizationException('Пароли не совпадают!');
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
            throw new AuthorizationException('Поле "Email" не заполнено');
        }
        if (empty($password)) {
            throw new AuthorizationException('Поле "Пароль" не заполнено');
        }

        $statement = $this->connection->prepare(
            'SELECT * FROM user WHERE email = :email'
        );
        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();
        if (empty($user)) {
            throw new AuthorizationException('Пользователь с такой почтой не найден');
        }

        if (password_verify($password, $user['password'])) {
            $this->session->setData('user', [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
            ]);
            return true;
        }

        throw new AuthorizationException('Неправильно введена почта или пароль');
    }

}
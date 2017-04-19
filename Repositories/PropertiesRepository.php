<?php
namespace PropertyAgent\Repositories;

use PDO;
use PDOException;
use PropertyAgent\Data\DbContext;

class PropertiesRepository
{
    public function get($id)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT * FROM `properties_view` WHERE `id` = ?');

            $statement
                ->bindParam(1, $id, PDO::PARAM_STR, 255);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        if (($property = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            return $property;
        }

        return 404;
    }

    public function getAll($limit, $offset)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT * FROM `properties_view` LIMIT ? OFFSET ?');

            $statement
                ->bindParam(1, $limit, PDO::PARAM_INT);

            $statement
                ->bindParam(2, $offset, PDO::PARAM_INT);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        $properties = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($properties !== false && !empty($properties)) {
            return $properties;
        }

        return 404;
    }

    public function getCount()
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT COUNT(*) AS `count` FROM `properties_view`');

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        $count = $statement->fetch(PDO::FETCH_ASSOC);

        if ($count !== false && !empty($count)) {
            return $count['count'];
        }

        return 0;
    }

    public function create(array $model)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('CALL createProperty(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

            $statement
                ->bindParam(1, $model['type'], PDO::PARAM_STR, 100);
            $statement
                ->bindParam(2, $model['road'], PDO::PARAM_STR, 200);
            $statement
                ->bindParam(3, $model['postal'], PDO::PARAM_STR, 4);
            $statement
                ->bindParam(4, $model['municipality'], PDO::PARAM_STR, 100);
            $statement
                ->bindParam(5, $model['number'], PDO::PARAM_INT);
            $statement
                ->bindParam(6, $model['floor'], PDO::PARAM_INT);
            $statement
                ->bindParam(7, $model['door'], PDO::PARAM_STR, 20);
            $statement
                ->bindParam(8, $model['rooms'], PDO::PARAM_INT);
            $statement
                ->bindParam(9, $model['area'], PDO::PARAM_INT);
            $statement
                ->bindParam(10, $model['year'], PDO::PARAM_STR, 10);
            $statement
                ->bindParam(11, $model['expenses'], PDO::PARAM_INT);
            $statement
                ->bindParam(12, $model['deposit'], PDO::PARAM_INT);
            $statement
                ->bindParam(13, $model['price'], PDO::PARAM_INT);
            $statement
                ->bindParam(14, $model['map'], PDO::PARAM_STR, 2083);
            $statement
                ->bindParam(15, $model['images']);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        return 201;
    }
}
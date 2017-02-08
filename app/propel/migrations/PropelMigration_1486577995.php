<?php

use Propel\Generator\Manager\MigrationManager;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1486577995.
 * Generated on 2017-02-08 19:19:55 by tomaszwojcik
 */
class PropelMigration_1486577995
{
    public $comment = '';

    public function preUp(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postUp(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    public function preDown(MigrationManager $manager)
    {
        // add the pre-migration code here
    }

    public function postDown(MigrationManager $manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `project`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(255) CHARACTER SET \'ascii\' COLLATE \'ascii_general_ci\' NOT NULL,
    `name` TEXT NOT NULL,
    `repository_uri` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `key` (`key`)
) ENGINE=InnoDB CHARACTER SET=\'utf8mb4\' COLLATE=\'utf8mb4_general_ci\';

CREATE TABLE `stage`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `project_id` INTEGER NOT NULL,
    `key` VARCHAR(255) CHARACTER SET \'ascii\' COLLATE \'ascii_general_ci\' NOT NULL,
    `name` TEXT NOT NULL,
    `tracked_branch` VARCHAR(255) CHARACTER SET \'ascii\' COLLATE \'ascii_general_ci\' NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `project_id_key` (`project_id`, `key`),
    CONSTRAINT `stage_FK1`
        FOREIGN KEY (`project_id`)
        REFERENCES `project` (`id`)
        ON UPDATE CASCADE
) ENGINE=InnoDB CHARACTER SET=\'utf8mb4\' COLLATE=\'utf8mb4_general_ci\';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'default' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `project`;

DROP TABLE IF EXISTS `stage`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
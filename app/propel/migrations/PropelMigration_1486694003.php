<?php

use Propel\Generator\Manager\MigrationManager;

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1486694003.
 * Generated on 2017-02-10 03:33:23 by tomaszwojcik
 */
class PropelMigration_1486694003
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

DROP INDEX `repository_owner_repository_name` ON `project`;

ALTER TABLE `project`

  CHANGE `repository_owner` `owner` VARCHAR(255) CHARACTER SET \'ascii\' COLLATE \'ascii_general_ci\' NOT NULL,

  CHANGE `repository_name` `repo` VARCHAR(255) CHARACTER SET \'ascii\' COLLATE \'ascii_general_ci\' NOT NULL,

  DROP `name`,

  DROP `repository_uri`;

CREATE UNIQUE INDEX `owner_repo` ON `project` (`owner`, `repo`);

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

DROP INDEX `owner_repo` ON `project`;

ALTER TABLE `project`

  CHANGE `owner` `repository_owner` VARCHAR(255) NOT NULL,

  CHANGE `repo` `repository_name` VARCHAR(255) NOT NULL,

  ADD `name` TEXT NOT NULL AFTER `id`,

  ADD `repository_uri` VARCHAR(255) NOT NULL AFTER `repository_name`;

CREATE UNIQUE INDEX `repository_owner_repository_name` ON `project` (`repository_owner`, `repository_name`);

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}
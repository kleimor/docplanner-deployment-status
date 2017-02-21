<?php

namespace AppBundle\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use AppBundle\Model\GithubWebhook as ChildGithubWebhook;
use AppBundle\Model\GithubWebhookQuery as ChildGithubWebhookQuery;
use AppBundle\Model\Project as ChildProject;
use AppBundle\Model\ProjectQuery as ChildProjectQuery;
use AppBundle\Model\Stage as ChildStage;
use AppBundle\Model\StageQuery as ChildStageQuery;
use AppBundle\Model\Map\GithubWebhookTableMap;
use AppBundle\Model\Map\ProjectTableMap;
use AppBundle\Model\Map\StageTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;

/**
 * Base class that represents a row from the 'project' table.
 *
 *
 *
 * @package    propel.generator.src.AppBundle.Model.Base
 */
abstract class Project implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\AppBundle\\Model\\Map\\ProjectTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the owner field.
     *
     * @var        string
     */
    protected $owner;

    /**
     * The value for the repo field.
     *
     * @var        string
     */
    protected $repo;

    /**
     * The value for the base_branch field.
     *
     * @var        string
     */
    protected $base_branch;

    /**
     * The value for the created_at field.
     *
     * @var        DateTime
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     *
     * @var        DateTime
     */
    protected $updated_at;

    /**
     * @var        ObjectCollection|ChildGithubWebhook[] Collection to store aggregation of ChildGithubWebhook objects.
     */
    protected $collGithubWebhooks;
    protected $collGithubWebhooksPartial;

    /**
     * @var        ObjectCollection|ChildStage[] Collection to store aggregation of ChildStage objects.
     */
    protected $collStages;
    protected $collStagesPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildGithubWebhook[]
     */
    protected $githubWebhooksScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildStage[]
     */
    protected $stagesScheduledForDeletion = null;

    /**
     * Initializes internal state of AppBundle\Model\Base\Project object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Project</code> instance.  If
     * <code>obj</code> is an instance of <code>Project</code>, delegates to
     * <code>equals(Project)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|Project The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [owner] column value.
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get the [repo] column value.
     *
     * @return string
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * Get the [base_branch] column value.
     *
     * @return string
     */
    public function getBaseBranch()
    {
        return $this->base_branch;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTimeInterface ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTimeInterface ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ProjectTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [owner] column.
     *
     * @param string $v new value
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setOwner($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->owner !== $v) {
            $this->owner = $v;
            $this->modifiedColumns[ProjectTableMap::COL_OWNER] = true;
        }

        return $this;
    } // setOwner()

    /**
     * Set the value of [repo] column.
     *
     * @param string $v new value
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setRepo($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->repo !== $v) {
            $this->repo = $v;
            $this->modifiedColumns[ProjectTableMap::COL_REPO] = true;
        }

        return $this;
    } // setRepo()

    /**
     * Set the value of [base_branch] column.
     *
     * @param string $v new value
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setBaseBranch($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->base_branch !== $v) {
            $this->base_branch = $v;
            $this->modifiedColumns[ProjectTableMap::COL_BASE_BRANCH] = true;
        }

        return $this;
    } // setBaseBranch()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($this->created_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->created_at->format("Y-m-d H:i:s.u")) {
                $this->created_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[ProjectTableMap::COL_CREATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTimeInterface value.
     *               Empty strings are treated as NULL.
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($this->updated_at === null || $dt === null || $dt->format("Y-m-d H:i:s.u") !== $this->updated_at->format("Y-m-d H:i:s.u")) {
                $this->updated_at = $dt === null ? null : clone $dt;
                $this->modifiedColumns[ProjectTableMap::COL_UPDATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setUpdatedAt()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ProjectTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ProjectTableMap::translateFieldName('Owner', TableMap::TYPE_PHPNAME, $indexType)];
            $this->owner = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ProjectTableMap::translateFieldName('Repo', TableMap::TYPE_PHPNAME, $indexType)];
            $this->repo = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ProjectTableMap::translateFieldName('BaseBranch', TableMap::TYPE_PHPNAME, $indexType)];
            $this->base_branch = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ProjectTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ProjectTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 6; // 6 = ProjectTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\AppBundle\\Model\\Project'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProjectTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildProjectQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collGithubWebhooks = null;

            $this->collStages = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Project::setDeleted()
     * @see Project::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProjectTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildProjectQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProjectTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior

                if (!$this->isColumnModified(ProjectTableMap::COL_CREATED_AT)) {
                    $this->setCreatedAt(\Propel\Runtime\Util\PropelDateTime::createHighPrecision());
                }
                if (!$this->isColumnModified(ProjectTableMap::COL_UPDATED_AT)) {
                    $this->setUpdatedAt(\Propel\Runtime\Util\PropelDateTime::createHighPrecision());
                }
            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(ProjectTableMap::COL_UPDATED_AT)) {
                    $this->setUpdatedAt(\Propel\Runtime\Util\PropelDateTime::createHighPrecision());
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ProjectTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->githubWebhooksScheduledForDeletion !== null) {
                if (!$this->githubWebhooksScheduledForDeletion->isEmpty()) {
                    \AppBundle\Model\GithubWebhookQuery::create()
                        ->filterByPrimaryKeys($this->githubWebhooksScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->githubWebhooksScheduledForDeletion = null;
                }
            }

            if ($this->collGithubWebhooks !== null) {
                foreach ($this->collGithubWebhooks as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->stagesScheduledForDeletion !== null) {
                if (!$this->stagesScheduledForDeletion->isEmpty()) {
                    \AppBundle\Model\StageQuery::create()
                        ->filterByPrimaryKeys($this->stagesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->stagesScheduledForDeletion = null;
                }
            }

            if ($this->collStages !== null) {
                foreach ($this->collStages as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[ProjectTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ProjectTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ProjectTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = '`id`';
        }
        if ($this->isColumnModified(ProjectTableMap::COL_OWNER)) {
            $modifiedColumns[':p' . $index++]  = '`owner`';
        }
        if ($this->isColumnModified(ProjectTableMap::COL_REPO)) {
            $modifiedColumns[':p' . $index++]  = '`repo`';
        }
        if ($this->isColumnModified(ProjectTableMap::COL_BASE_BRANCH)) {
            $modifiedColumns[':p' . $index++]  = '`base_branch`';
        }
        if ($this->isColumnModified(ProjectTableMap::COL_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`created_at`';
        }
        if ($this->isColumnModified(ProjectTableMap::COL_UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`updated_at`';
        }

        $sql = sprintf(
            'INSERT INTO `project` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id`':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case '`owner`':
                        $stmt->bindValue($identifier, $this->owner, PDO::PARAM_STR);
                        break;
                    case '`repo`':
                        $stmt->bindValue($identifier, $this->repo, PDO::PARAM_STR);
                        break;
                    case '`base_branch`':
                        $stmt->bindValue($identifier, $this->base_branch, PDO::PARAM_STR);
                        break;
                    case '`created_at`':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                    case '`updated_at`':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s.u") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ProjectTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getOwner();
                break;
            case 2:
                return $this->getRepo();
                break;
            case 3:
                return $this->getBaseBranch();
                break;
            case 4:
                return $this->getCreatedAt();
                break;
            case 5:
                return $this->getUpdatedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['Project'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Project'][$this->hashCode()] = true;
        $keys = ProjectTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getOwner(),
            $keys[2] => $this->getRepo(),
            $keys[3] => $this->getBaseBranch(),
            $keys[4] => $this->getCreatedAt(),
            $keys[5] => $this->getUpdatedAt(),
        );
        if ($result[$keys[4]] instanceof \DateTime) {
            $result[$keys[4]] = $result[$keys[4]]->format('c');
        }

        if ($result[$keys[5]] instanceof \DateTime) {
            $result[$keys[5]] = $result[$keys[5]]->format('c');
        }

        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collGithubWebhooks) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'githubWebhooks';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'github_webhooks';
                        break;
                    default:
                        $key = 'GithubWebhooks';
                }

                $result[$key] = $this->collGithubWebhooks->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collStages) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'stages';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'stages';
                        break;
                    default:
                        $key = 'Stages';
                }

                $result[$key] = $this->collStages->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\AppBundle\Model\Project
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ProjectTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\AppBundle\Model\Project
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setOwner($value);
                break;
            case 2:
                $this->setRepo($value);
                break;
            case 3:
                $this->setBaseBranch($value);
                break;
            case 4:
                $this->setCreatedAt($value);
                break;
            case 5:
                $this->setUpdatedAt($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = ProjectTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setOwner($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setRepo($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setBaseBranch($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setCreatedAt($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setUpdatedAt($arr[$keys[5]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\AppBundle\Model\Project The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ProjectTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ProjectTableMap::COL_ID)) {
            $criteria->add(ProjectTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ProjectTableMap::COL_OWNER)) {
            $criteria->add(ProjectTableMap::COL_OWNER, $this->owner);
        }
        if ($this->isColumnModified(ProjectTableMap::COL_REPO)) {
            $criteria->add(ProjectTableMap::COL_REPO, $this->repo);
        }
        if ($this->isColumnModified(ProjectTableMap::COL_BASE_BRANCH)) {
            $criteria->add(ProjectTableMap::COL_BASE_BRANCH, $this->base_branch);
        }
        if ($this->isColumnModified(ProjectTableMap::COL_CREATED_AT)) {
            $criteria->add(ProjectTableMap::COL_CREATED_AT, $this->created_at);
        }
        if ($this->isColumnModified(ProjectTableMap::COL_UPDATED_AT)) {
            $criteria->add(ProjectTableMap::COL_UPDATED_AT, $this->updated_at);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildProjectQuery::create();
        $criteria->add(ProjectTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \AppBundle\Model\Project (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setOwner($this->getOwner());
        $copyObj->setRepo($this->getRepo());
        $copyObj->setBaseBranch($this->getBaseBranch());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getGithubWebhooks() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGithubWebhook($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getStages() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addStage($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \AppBundle\Model\Project Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('GithubWebhook' == $relationName) {
            return $this->initGithubWebhooks();
        }
        if ('Stage' == $relationName) {
            return $this->initStages();
        }
    }

    /**
     * Clears out the collGithubWebhooks collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addGithubWebhooks()
     */
    public function clearGithubWebhooks()
    {
        $this->collGithubWebhooks = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collGithubWebhooks collection loaded partially.
     */
    public function resetPartialGithubWebhooks($v = true)
    {
        $this->collGithubWebhooksPartial = $v;
    }

    /**
     * Initializes the collGithubWebhooks collection.
     *
     * By default this just sets the collGithubWebhooks collection to an empty array (like clearcollGithubWebhooks());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGithubWebhooks($overrideExisting = true)
    {
        if (null !== $this->collGithubWebhooks && !$overrideExisting) {
            return;
        }

        $collectionClassName = GithubWebhookTableMap::getTableMap()->getCollectionClassName();

        $this->collGithubWebhooks = new $collectionClassName;
        $this->collGithubWebhooks->setModel('\AppBundle\Model\GithubWebhook');
    }

    /**
     * Gets an array of ChildGithubWebhook objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildProject is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildGithubWebhook[] List of ChildGithubWebhook objects
     * @throws PropelException
     */
    public function getGithubWebhooks(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collGithubWebhooksPartial && !$this->isNew();
        if (null === $this->collGithubWebhooks || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGithubWebhooks) {
                // return empty collection
                $this->initGithubWebhooks();
            } else {
                $collGithubWebhooks = ChildGithubWebhookQuery::create(null, $criteria)
                    ->filterByProject($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collGithubWebhooksPartial && count($collGithubWebhooks)) {
                        $this->initGithubWebhooks(false);

                        foreach ($collGithubWebhooks as $obj) {
                            if (false == $this->collGithubWebhooks->contains($obj)) {
                                $this->collGithubWebhooks->append($obj);
                            }
                        }

                        $this->collGithubWebhooksPartial = true;
                    }

                    return $collGithubWebhooks;
                }

                if ($partial && $this->collGithubWebhooks) {
                    foreach ($this->collGithubWebhooks as $obj) {
                        if ($obj->isNew()) {
                            $collGithubWebhooks[] = $obj;
                        }
                    }
                }

                $this->collGithubWebhooks = $collGithubWebhooks;
                $this->collGithubWebhooksPartial = false;
            }
        }

        return $this->collGithubWebhooks;
    }

    /**
     * Sets a collection of ChildGithubWebhook objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $githubWebhooks A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildProject The current object (for fluent API support)
     */
    public function setGithubWebhooks(Collection $githubWebhooks, ConnectionInterface $con = null)
    {
        /** @var ChildGithubWebhook[] $githubWebhooksToDelete */
        $githubWebhooksToDelete = $this->getGithubWebhooks(new Criteria(), $con)->diff($githubWebhooks);


        $this->githubWebhooksScheduledForDeletion = $githubWebhooksToDelete;

        foreach ($githubWebhooksToDelete as $githubWebhookRemoved) {
            $githubWebhookRemoved->setProject(null);
        }

        $this->collGithubWebhooks = null;
        foreach ($githubWebhooks as $githubWebhook) {
            $this->addGithubWebhook($githubWebhook);
        }

        $this->collGithubWebhooks = $githubWebhooks;
        $this->collGithubWebhooksPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GithubWebhook objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related GithubWebhook objects.
     * @throws PropelException
     */
    public function countGithubWebhooks(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collGithubWebhooksPartial && !$this->isNew();
        if (null === $this->collGithubWebhooks || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGithubWebhooks) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGithubWebhooks());
            }

            $query = ChildGithubWebhookQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByProject($this)
                ->count($con);
        }

        return count($this->collGithubWebhooks);
    }

    /**
     * Method called to associate a ChildGithubWebhook object to this object
     * through the ChildGithubWebhook foreign key attribute.
     *
     * @param  ChildGithubWebhook $l ChildGithubWebhook
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function addGithubWebhook(ChildGithubWebhook $l)
    {
        if ($this->collGithubWebhooks === null) {
            $this->initGithubWebhooks();
            $this->collGithubWebhooksPartial = true;
        }

        if (!$this->collGithubWebhooks->contains($l)) {
            $this->doAddGithubWebhook($l);

            if ($this->githubWebhooksScheduledForDeletion and $this->githubWebhooksScheduledForDeletion->contains($l)) {
                $this->githubWebhooksScheduledForDeletion->remove($this->githubWebhooksScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildGithubWebhook $githubWebhook The ChildGithubWebhook object to add.
     */
    protected function doAddGithubWebhook(ChildGithubWebhook $githubWebhook)
    {
        $this->collGithubWebhooks[]= $githubWebhook;
        $githubWebhook->setProject($this);
    }

    /**
     * @param  ChildGithubWebhook $githubWebhook The ChildGithubWebhook object to remove.
     * @return $this|ChildProject The current object (for fluent API support)
     */
    public function removeGithubWebhook(ChildGithubWebhook $githubWebhook)
    {
        if ($this->getGithubWebhooks()->contains($githubWebhook)) {
            $pos = $this->collGithubWebhooks->search($githubWebhook);
            $this->collGithubWebhooks->remove($pos);
            if (null === $this->githubWebhooksScheduledForDeletion) {
                $this->githubWebhooksScheduledForDeletion = clone $this->collGithubWebhooks;
                $this->githubWebhooksScheduledForDeletion->clear();
            }
            $this->githubWebhooksScheduledForDeletion[]= clone $githubWebhook;
            $githubWebhook->setProject(null);
        }

        return $this;
    }

    /**
     * Clears out the collStages collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addStages()
     */
    public function clearStages()
    {
        $this->collStages = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collStages collection loaded partially.
     */
    public function resetPartialStages($v = true)
    {
        $this->collStagesPartial = $v;
    }

    /**
     * Initializes the collStages collection.
     *
     * By default this just sets the collStages collection to an empty array (like clearcollStages());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initStages($overrideExisting = true)
    {
        if (null !== $this->collStages && !$overrideExisting) {
            return;
        }

        $collectionClassName = StageTableMap::getTableMap()->getCollectionClassName();

        $this->collStages = new $collectionClassName;
        $this->collStages->setModel('\AppBundle\Model\Stage');
    }

    /**
     * Gets an array of ChildStage objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildProject is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildStage[] List of ChildStage objects
     * @throws PropelException
     */
    public function getStages(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collStagesPartial && !$this->isNew();
        if (null === $this->collStages || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collStages) {
                // return empty collection
                $this->initStages();
            } else {
                $collStages = ChildStageQuery::create(null, $criteria)
                    ->filterByProject($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collStagesPartial && count($collStages)) {
                        $this->initStages(false);

                        foreach ($collStages as $obj) {
                            if (false == $this->collStages->contains($obj)) {
                                $this->collStages->append($obj);
                            }
                        }

                        $this->collStagesPartial = true;
                    }

                    return $collStages;
                }

                if ($partial && $this->collStages) {
                    foreach ($this->collStages as $obj) {
                        if ($obj->isNew()) {
                            $collStages[] = $obj;
                        }
                    }
                }

                $this->collStages = $collStages;
                $this->collStagesPartial = false;
            }
        }

        return $this->collStages;
    }

    /**
     * Sets a collection of ChildStage objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $stages A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildProject The current object (for fluent API support)
     */
    public function setStages(Collection $stages, ConnectionInterface $con = null)
    {
        /** @var ChildStage[] $stagesToDelete */
        $stagesToDelete = $this->getStages(new Criteria(), $con)->diff($stages);


        $this->stagesScheduledForDeletion = $stagesToDelete;

        foreach ($stagesToDelete as $stageRemoved) {
            $stageRemoved->setProject(null);
        }

        $this->collStages = null;
        foreach ($stages as $stage) {
            $this->addStage($stage);
        }

        $this->collStages = $stages;
        $this->collStagesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Stage objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Stage objects.
     * @throws PropelException
     */
    public function countStages(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collStagesPartial && !$this->isNew();
        if (null === $this->collStages || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collStages) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getStages());
            }

            $query = ChildStageQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByProject($this)
                ->count($con);
        }

        return count($this->collStages);
    }

    /**
     * Method called to associate a ChildStage object to this object
     * through the ChildStage foreign key attribute.
     *
     * @param  ChildStage $l ChildStage
     * @return $this|\AppBundle\Model\Project The current object (for fluent API support)
     */
    public function addStage(ChildStage $l)
    {
        if ($this->collStages === null) {
            $this->initStages();
            $this->collStagesPartial = true;
        }

        if (!$this->collStages->contains($l)) {
            $this->doAddStage($l);

            if ($this->stagesScheduledForDeletion and $this->stagesScheduledForDeletion->contains($l)) {
                $this->stagesScheduledForDeletion->remove($this->stagesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildStage $stage The ChildStage object to add.
     */
    protected function doAddStage(ChildStage $stage)
    {
        $this->collStages[]= $stage;
        $stage->setProject($this);
    }

    /**
     * @param  ChildStage $stage The ChildStage object to remove.
     * @return $this|ChildProject The current object (for fluent API support)
     */
    public function removeStage(ChildStage $stage)
    {
        if ($this->getStages()->contains($stage)) {
            $pos = $this->collStages->search($stage);
            $this->collStages->remove($pos);
            if (null === $this->stagesScheduledForDeletion) {
                $this->stagesScheduledForDeletion = clone $this->collStages;
                $this->stagesScheduledForDeletion->clear();
            }
            $this->stagesScheduledForDeletion[]= clone $stage;
            $stage->setProject(null);
        }

        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->id = null;
        $this->owner = null;
        $this->repo = null;
        $this->base_branch = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collGithubWebhooks) {
                foreach ($this->collGithubWebhooks as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collStages) {
                foreach ($this->collStages as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collGithubWebhooks = null;
        $this->collStages = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ProjectTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     $this|ChildProject The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[ProjectTableMap::COL_UPDATED_AT] = true;

        return $this;
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}

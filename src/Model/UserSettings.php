<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2018 Serhii Popov
 * This source file is subject to The MIT License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @category Popov
 * @package Popov_ZfcDataGrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcDataGrid\Model;

use Doctrine\ORM\Mapping as ORM;
use Popov\ZfcCore\Model\DomainAwareTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_grid_settings")
 */
class UserSettings
{
    const MNEMO = 'gridSettings';

    const TABLE = 'user_grid_settings';

    use DomainAwareTrait;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="userId", type="integer", nullable=false)
     */
    private $userId;
    /**
     * @var int
     * @ORM\Column(name="gridId", type="string", nullable=false, length=50)
     */
    private $gridId;

    /**
     * @var string
     * @ORM\Column(name="columns", type="string", nullable=false, length=255)
     */
    private $columns;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserSettings
     */
    public function setId(int $id): UserSettings
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return UserSettings
     */
    public function setUserId(int $userId): UserSettings
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getGridId(): string
    {
        return $this->gridId;
    }

    /**
     * @param string $gridId
     * @return UserSettings
     */
    public function setGridId(string $gridId): UserSettings
    {
        $this->gridId = $gridId;

        return $this;
    }

    /**
     * @return string
     */
    public function getColumns(): string
    {
        return $this->columns;
    }

    /**
     * @param string $columns
     * @return UserSettings
     */
    public function setColumns(string $columns): UserSettings
    {
        $this->columns = $columns;

        return $this;
    }
}
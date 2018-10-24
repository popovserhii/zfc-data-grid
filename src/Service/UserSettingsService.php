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
 * @package Popov_ZfcDataDrid
 * @author Serhii Popov <popow.serhii@gmail.com>
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Popov\ZfcDataGrid\Service;

use Doctrine\ORM\EntityManager;
use Popov\ZfcCore\Service\DomainServiceAbstract;
use Popov\ZfcDataGrid\Model\UserSettings;
use Popov\ZfcUser\Helper\UserHelper;
use ZfcDatagrid\Column\AbstractColumn;

class UserSettingsService extends DomainServiceAbstract
{
    protected $entity = UserSettings::class;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    protected $settings;

    public function __construct(EntityManager $entityManager, UserHelper $userHelper)
    {
        $this->entityManager = $entityManager;
        $this->userHelper = $userHelper;
    }

    public function apply(AbstractColumn $column, $gridId)
    {
        $settings = $this->getSettings($gridId);

        if ($settings) {
            $hiddenColumns = json_decode($settings[0]->getColumns(), true);
            foreach ($hiddenColumns as $name => $value) {
                if ($name == $column->getUniqueId()) {
                    if ($value['hidden'] == true) {
                        $column->setHidden(true);
                    }
                }
            }
        }
    }

    protected function getSettings($gridId)
    {
        if (!$this->settings) {
            $this->settings = $this->getRepository()
                ->findBy(['userId' => $this->userHelper->current()->getId(), 'gridId' => $gridId]);
        }
        return $this->settings;
    }
}
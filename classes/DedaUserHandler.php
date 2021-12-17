<?php

class DedaUserHandler
{
    protected $accountAttributeIdentifier;
    /**
     * @var eZContentClass
     */
    private $userClass;
    private $fiscalCode;
    private $email;
    private $login;
    private $attributes = [];

    public function __construct($userData)
    {
        $userData['fiscalNumber'] = str_replace('TINIT-', '', $userData['fiscalNumber']);
        $mapper = eZINI::instance('dedalogin.ini')->group('AttributeMapper');
        $this->fiscalCode = $userData[$mapper['FiscalCode']];
        $this->login = $userData[$mapper['UserLogin']];
        $this->email = $userData[$mapper['UserEmail']];

        foreach ($mapper['Attributes'] as $key => $map) {
            $this->attributes[$key] = $userData[$map];
        }

        foreach ($this->getUserClass()->dataMap() as $identifier => $classAttribute) {
            if ($classAttribute->attribute('data_type_string') == eZUserType::DATA_TYPE_STRING) {
                $this->accountAttributeIdentifier = $identifier;
            }
        }
    }

    private function getUserClass()
    {
        if ($this->userClass === null) {
            $ini = eZINI::instance();
            $this->userClass = eZContentClass::fetch($ini->variable("UserSettings", "UserClassID"));
            if (!$this->userClass instanceof eZContentClass) {
                throw new Exception('User class not found');
            }
        }

        return $this->userClass;
    }

    public function login()
    {
        $user = $this->getExistingUser();

        if ($user instanceof eZUser) {
            $userObject = $user->contentObject();
            if ($userObject instanceof eZContentObject) {
                $this->log('debug', 'Auth user exist: update user data', __METHOD__);

                if (!$userObject->mainNodeID()) {
                    if (count($userObject->assignedNodes()) === 0) {
                        $nodeAssignment = eZNodeAssignment::create([
                            'contentobject_id' => $userObject->attribute('id'),
                            'contentobject_version' => $userObject->attribute('current_version'),
                            'parent_node' => (int)eZINI::instance()->variable("UserSettings", "DefaultUserPlacement"),
                            'is_main' => 1,
                        ]);
                        $nodeAssignment->store();
                        eZContentOperationCollection::publishNode($nodeAssignment->attribute('parent_node'), $userObject->attribute('id'), $userObject->attribute('current_version'), false);
                        $this->log('debug', 'Force set main node to user', __METHOD__);
                    } else {
                        eZUserOperationCollection::publishUserContentObject($user->id());
                        eZUserOperationCollection::sendUserNotification($user->id());
                        $this->log('debug', 'Force publish user and send notification', __METHOD__);
                    }
                    if ($user->attribute('email') !== $this->email) {
                        $userByEmail = eZUser::fetchByEmail($this->email);
                        if (!$userByEmail) {
                            $user->setAttribute('email', $this->email);
                            $user->store();
                            $this->log('debug', 'Update user email', __METHOD__);
                        }
                    }
                }
                eZContentFunctions::updateAndPublishObject($user->contentObject(), ['attributes' => $this->attributes]);

                $this->loginUser($user);

                return $user;

            } else {
                eZUser::removeUser($user->id());
            }
        }

        $this->log('debug', 'Auth user does not exist: create user', __METHOD__);

        $this->attributes[$this->accountAttributeIdentifier] = $this->login . '|' . $this->email . '||' . eZUser::passwordHashTypeName(eZUser::hashType()) . '|1';
        $params = [];
        $params['creator_id'] = $this->getUserCreatorId();
        $params['class_identifier'] = $this->getUserClass()->attribute('identifier');
        $params['parent_node_id'] = $this->getUserParentNodeId();
        $params['attributes'] = $this->attributes;

        $contentObject = eZContentFunctions::createAndPublishObject($params);

        if ($contentObject instanceof eZContentObject) {
            $user = eZUser::fetch($contentObject->attribute('id'));
            if ($user instanceof eZUser) {
                $dedaUser = $this->getExistingUser();
                if ($dedaUser instanceof eZUser && $dedaUser->id() == $user->id()) {
                    $this->loginUser($user);
                    eZUserOperationCollection::sendUserNotification($user->id());
                    return $user;
                }
            }
        }

        throw new Exception("Error creating user", 1);
    }

    public function handleRedirect(eZModule $module, eZUser $user)
    {
        $ini = eZINI::instance();
        $redirectionURI = $ini->variable('SiteSettings', 'DefaultPage');
        if (is_object($user)) {
            /*
             * Choose where to redirect the user to after successful login.
             * The checks are done in the following order:
             * 1. Per-user.
             * 2. Per-group.
             *    If the user object is published under several groups, main node is chosen
             *    (it its URI non-empty; otherwise first non-empty URI is chosen from the group list -- if any).
             *
             * See doc/features/3.8/advanced_redirection_after_user_login.txt for more information.
             */

            // First, let's determine which attributes we should search redirection URI in.
            $userUriAttrName = '';
            $groupUriAttrName = '';
            if ($ini->hasVariable('UserSettings', 'LoginRedirectionUriAttribute')) {
                $uriAttrNames = $ini->variable('UserSettings', 'LoginRedirectionUriAttribute');
                if (is_array($uriAttrNames)) {
                    if (isset($uriAttrNames['user'])) {
                        $userUriAttrName = $uriAttrNames['user'];
                    }

                    if (isset($uriAttrNames['group'])) {
                        $groupUriAttrName = $uriAttrNames['group'];
                    }
                }
            }

            $userObject = $user->attribute('contentobject');

            // 1. Check if redirection URI is specified for the user
            $userUriSpecified = false;
            if ($userUriAttrName) {
                /** @var eZContentObjectAttribute[] $userDataMap */
                $userDataMap = $userObject->attribute('data_map');
                if (!isset($userDataMap[$userUriAttrName])) {
                    $this->log('warning', "Cannot find redirection URI: there is no attribute '$userUriAttrName' in object '" .
                        $userObject->attribute('name') .
                        "' of class '" .
                        $userObject->attribute('class_name') . "'.");
                } elseif (($uriAttribute = $userDataMap[$userUriAttrName])
                    && ($uri = $uriAttribute->attribute('content'))) {
                    $redirectionURI = $uri;
                    $userUriSpecified = true;
                }
            }

            // 2.Check if redirection URI is specified for at least one of the user's groups (preferring main parent group).
            if (!$userUriSpecified && $groupUriAttrName && $user->hasAttribute('groups')) {
                $groups = $user->attribute('groups');

                if (isset($groups) && is_array($groups)) {
                    $chosenGroupURI = '';
                    foreach ($groups as $groupID) {
                        $group = eZContentObject::fetch($groupID);
                        /** @var eZContentObjectAttribute[] $groupDataMap */
                        $groupDataMap = $group->attribute('data_map');
                        $isMainParent = ($group->attribute('main_node_id') == $userObject->attribute('main_parent_node_id'));

                        if (!isset($groupDataMap[$groupUriAttrName])) {
                            $this->log('warning', "Cannot find redirection URI: there is no attribute '$groupUriAttrName' in object '" .
                                $group->attribute('name') .
                                "' of class '" .
                                $group->attribute('class_name') . "'.");
                            continue;
                        }
                        $uri = $groupDataMap[$groupUriAttrName]->attribute('content');
                        if ($uri) {
                            if ($isMainParent) {
                                $chosenGroupURI = $uri;
                                break;
                            } elseif (!$chosenGroupURI) {
                                $chosenGroupURI = $uri;
                            }
                        }
                    }

                    if ($chosenGroupURI) // if we've chose an URI from one of the user's groups.
                    {
                        $redirectionURI = $chosenGroupURI;
                    }
                }
            }
        }

        $module->redirectTo($redirectionURI);
        return true;
    }

    private function getExistingUser()
    {
        $user = eZUser::fetchByName($this->login);
        if (!$user instanceof eZUser) {
            $user = $this->getUserByFiscalCode();
        }
        if (!$user instanceof eZUser) {
            $user = eZUser::fetchByEmail($this->email);
        }

        return $user;
    }

    private function getUserByFiscalCode()
    {
        $user = false;

        if (class_exists('OCCodiceFiscaleType')) {
            /** @var eZContentClassAttribute $attribute */
            foreach ($this->getUserClass()->dataMap() as $attribute) {
                if ($attribute->attribute('data_type_string') == OCCodiceFiscaleType::DATA_TYPE_STRING) {
                    $userObject = $this->fetchObjectByFiscalCode($attribute->attribute('id'));
                    if ($userObject instanceof eZContentObject) {
                        $user = eZUser::fetch($userObject->attribute('id'));
                    }
                }
            }
        }

        return $user;
    }

    private function fetchObjectByFiscalCode($contentClassAttributeID)
    {
        $query = "SELECT co.id
				FROM ezcontentobject co, ezcontentobject_attribute coa
				WHERE co.id = coa.contentobject_id
				AND co.current_version = coa.version								
				AND coa.contentclassattribute_id = " . intval($contentClassAttributeID) . "
				AND UPPER(coa.data_text) = '" . eZDB::instance()->escapeString(strtoupper($this->fiscalCode)) . "'";

        $result = eZDB::instance()->arrayQuery($query);
        if (isset($result[0]['id'])) {
            return eZContentObject::fetch((int)$result[0]['id']);
        }

        return false;
    }

    private function getUserByEmail()
    {
        return eZUser::fetchByEmail($this->email);
    }

    private function log($level, $message, $context)
    {
        if ($level == 'error') {
            eZDebug::writeError($message, $context);
            eZLog::write("[error] $message", 'ocdedalogin.log');
        }
        if ($level == 'warning') {
            eZDebug::writeWarning($message, $context);
            eZLog::write("[warning] $message", 'ocdedalogin.log');
        }
        if ($level == 'notice') {
            eZDebug::writeNotice($message, $context);
            eZLog::write("[notice] $message", 'ocdedalogin.log');
        }
        if ($level == 'debug') {
            eZDebug::writeDebug($message, $context);
            eZLog::write("[debug] $message", 'ocdedalogin.log');
        }
    }

    private function loginUser(eZUser $user)
    {
        $userID = $user->attribute('contentobject_id');

        // if audit is enabled logins should be logged
        eZAudit::writeAudit('user-login', ['User id' => $userID, 'User login' => $user->attribute('login')]);

        eZUser::updateLastVisit($userID, true);
        eZUser::setCurrentlyLoggedInUser($user, $userID);

        // Reset number of failed login attempts
        eZUser::setFailedLoginAttempts($userID, 0);

        eZHTTPTool::instance()->setSessionVariable('DEDAUserLoggedIn', true);
    }

    private function getUserCreatorId()
    {
        $ini = eZINI::instance();

        return $ini->variable("UserSettings", "UserCreatorID");
    }

    private function getUserParentNodeId()
    {
        $ini = eZINI::instance();
        $db = eZDB::instance();
        $defaultUserPlacement = (int)$ini->variable("UserSettings", "DefaultUserPlacement");
        $sql = "SELECT count(*) as count FROM ezcontentobject_tree WHERE node_id = $defaultUserPlacement";
        $rows = $db->arrayQuery($sql);
        $count = $rows[0]['count'];
        if ($count < 1) {
            $errMsg = ezpI18n::tr('design/standard/user',
                'The node (%1) specified in [UserSettings].DefaultUserPlacement setting in site.ini does not exist!',
                null, [$defaultUserPlacement]);
            throw new Exception($errMsg, 1);
        }

        return $defaultUserPlacement;
    }
}

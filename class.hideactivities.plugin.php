<?php if (!defined('APPLICATION')) exit();
/*
Copyright 2013 Alessandro Miliucci <lifeisfoo@gmail.com>
This file is part of HideActivities <https://github.com/lifeisfoo/HideActivities>

HideActivities is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

HideActivities is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with HideActivities. If not, see <http://www.gnu.org/licenses/>.
*/

// Define the plugin:
$PluginInfo['HideActivities'] = array(
	'Name' => 'Hide Activities',
	'Description' => 'Shows user activities (profile page) only to friends (from Friendships plugin)',
	'Version' => '0.2',
	'RequiredApplications' => array('Vanilla' => '2.0.18.4'),
	'RequiredTheme' => FALSE, 
	'RequiredPlugins' => array('Friendships' => '0.1'),
	'HasLocale' => FALSE,
	'SettingsUrl' => FALSE,
	'SettingsPermission' => 'Garden.AdminUser.Only',
	'Author' => "Alessandro Miliucci",
	'AuthorEmail' => 'lifeisfoo@gmail.com',
	'AuthorUrl' => 'http://forkwait.net'
	);

class HideActivitiesPlugin extends Gdn_Plugin {

	private $_FriendshipModel;

	public function __construct() {
		$this->_FriendshipModel = new FriendshipModel();
	}

	private function _SessionUserID(){
		if(!Gdn::Session()->IsValid()){
			return false;
		}else{
			return Gdn::Session()->UserID;
		}
	}

	private function _ProfilePageID($ProfileController){
		if($ProfileController instanceof ProfileController){
			return $ProfileController->User->UserID;
		}else{
			return false;
		}
	}

	private function _EmptyActivities(){
		return Gdn::SQL()->Select('*')
		                ->From('Activity a')
		                ->Limit(0, 0)
		                ->Get();
	}

	private function _FriendsActivities(){
		if(!Gdn::Session()->IsValid()){ //if guest
			return $this->_EmptyActivities();
		}else{ //friends activities
			$FriendsIDs = $this->_FriendshipModel->FriendsIDs(Gdn::Session()->UserID);
			return Gdn::SQL()
				//from ActivityModel->ActivityQuery()
				->Select('a.*')
				->Select('t.FullHeadline, t.ProfileHeadline, t.AllowComments, t.ShowIcon, t.RouteCode')
				->Select('t.Name', '', 'ActivityType')
				->Select('au.Name', '', 'ActivityName')
				->Select('au.Gender', '', 'ActivityGender')
				->Select('au.Photo', '', 'ActivityPhoto')
				->Select('au.Email', '', 'ActivityEmail')
				->Select('ru.Name', '', 'RegardingName')
				->Select('ru.Gender', '', 'RegardingGender')
				->Select('ru.Email', '', 'RegardingEmail')
				->Select('ru.Photo', '', 'RegardingPhoto')
				->From('Activity a')
				->Join('ActivityType t', 'a.ActivityTypeID = t.ActivityTypeID')
				->Join('User au', 'a.ActivityUserID = au.UserID')
				->Join('User ru', 'a.RegardingUserID = ru.UserID', 'left')
				//from ActivityModel->Get()
				->OrderBy('a.DateInserted', 'desc')
				//includes only activity from friends
				->WhereIn('a.ActivityUserID', $FriendsIDs)
				->Where('t.Public', '1')
				->Where('a.CommentActivityID is null')
				->Limit(50)
				->Get();
		}
	}

	public function ProfileController_BeforeActivitiesList_Handler($Sender) {
		if($this->_SessionUserID() && $this->_ProfilePageID($Sender)){
			if($this->_SessionUserID() != $this->_ProfilePageID($Sender)){ //not in himself profile page
				if(!$this->_FriendshipModel->FriendsFrom($this->_SessionUserID(), $this->_ProfilePageID($Sender))){
					//they are not friends
					$Sender->ActivityData = $this->_EmptyActivities();
				}else{ //they are good friends
					//do nothing
				}
			}else{ //user is on himself profile page
				//do nothing
			}
		}else{//guest
			$Sender->ActivityData = $this->_EmptyActivities();
		}
	}

	public function ActivityController_BeforeStatusForm_Handler($Sender) {
		$Sender->ActivityData = $this->_FriendsActivities();
	}

	public function Setup() {}

	public function OnDisable() {}

}

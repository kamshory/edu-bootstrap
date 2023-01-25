<?php


class MemberPage
{
	public $member_id = 0;
	public $username = '';
	public $name = '';
	public $gender = 'M';
	public $birth_place = '';
	public $birth_day = '';
	public $email = '';
	public $phone = '';
	public $url = '';
	public $img_360_compress = '';
	public $show_compass = '';
	public $autoplay_360 = '';
	public $autorotate_360 = '';
	public $picture_hash = '';
	public $image_url = '';
	public $image_url_50 = '';
	public $image_url_100 = '';
	public $background = '';
	public $circle_avatar = 0;
	public $country_id = '';
	public $state_id = '';
	public $city_id = '';
	public $language = 'ID';

	public function __construct($database, $username)
	{
		global $cfg;
		if (is_numeric($username)) {
			$sql = "SELECT `member_id`, `username`, `name`, `gender`, `birth_place`, `birth_day`, `email`, `phone`, `url`, `show_compass`, 
			`autoplay_360`, `autorotate_360`, `img_360_compress`, `picture_hash`, `background`, `language`, `country_id`, `state_id`, 
			`city_id`, `circle_avatar`
			from `member` where `member_id` = '$username' and `active` = '1' ";
		} else {
			$sql = "SELECT `member_id`, `username`, `name`, `gender`, `birth_place`, `birth_day`, `email`, `phone`, `url`, `show_compass`,
			`autoplay_360`, `autorotate_360`, `img_360_compress`, `picture_hash`, `background`, `language`, `country_id`, `state_id`, 
			`city_id`, `circle_avatar`
			from `member` where `username` = '$username' and `active` = '1' ";
		}
		$stmt = $database->executeQuery($sql);
		if ($stmt->rowCount() > 0) {
			$member_login = $stmt->fetchObject();
			$this->member_id = $member_login->member_id;
			$this->username = ($member_login->username != '') ? $member_login->username : $member_login->member_id;
			$this->name = trim($member_login->name);
			$this->gender = $member_login->gender;
			$this->birth_place = $member_login->birth_place;
			$this->birth_day = $member_login->birth_day;
			$this->email = $member_login->email;
			$this->phone = $member_login->phone;
			$this->url = $member_login->url;
			$this->img_360_compress = $member_login->img_360_compress;
			$this->autoplay_360 = $member_login->autoplay_360;
			$this->autorotate_360 = $member_login->autorotate_360;
			$this->show_compass = $member_login->show_compass;
			$this->background = $member_login->background;
			$this->circle_avatar = $member_login->circle_avatar;
			$this->language = $member_login->language;
			$this->country_id = $member_login->country_id;
			$this->state_id = $member_login->state_id;
			$this->city_id = $member_login->city_id;
			if ($member_login->picture_hash == '') {
				$this->image_url = $cfg->base_avatar . "__default/" . $member_login->gender . "/avatar.jpg";
				$this->image_url_50 = $cfg->base_avatar . "__default/" . $member_login->gender . "/uimage-50.jpg";
				$this->image_url_100 = $cfg->base_avatar . "__default/" . $member_login->gender . "/uimage-100.jpg";
			} else {
				$this->image_url = $cfg->base_avatar . "" . $member_login->member_id . "/avatar.jpg?hash=" . $member_login->picture_hash;
				$this->image_url_50 = $cfg->base_avatar . "" . $member_login->member_id . "/uimage-50.jpg?hash=" . $member_login->picture_hash;
				$this->image_url_100 = $cfg->base_avatar . "" . $member_login->member_id . "/uimage-100.jpg?hash=" . $member_login->picture_hash;
			}
		}
	}
}

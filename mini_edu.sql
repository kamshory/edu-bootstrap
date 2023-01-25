-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2023 at 01:03 PM
-- Server version: 10.11.0-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mini_edu`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_admin_password` (IN `in_username` VARCHAR(100), IN `in_password` VARCHAR(100))  NO SQL BEGIN
DECLARE 
`val_member_id` BIGINT DEFAULT NULL;


SELECT `admin_id` INTO `val_member_id` FROM `edu_admin` WHERE `username` like `in_username`;


IF `val_member_id` IS NOT NULL THEN
UPDATE `edu_admin` SET `password` = md5(md5(`in_password`)) WHERE `admin_id` = `val_member_id`;
UPDATE `member` SET `password` = md5(md5(`in_password`)) WHERE `member_id` = `val_member_id`;
END IF;


END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `city_id_to_name` (`in_city_id` BIGINT) RETURNS VARCHAR(100) CHARSET latin1 NO SQL begin


declare `out_name` varchar(100) default null;
select `city`.`name` into `out_name` from `city` where `city`.`city_id` = `in_city_id`;
return `out_name`;


end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `city_name_to_id` (`in_name` VARCHAR(100), `in_state_id` BIGINT, `in_country_id` VARCHAR(3)) RETURNS BIGINT(20) NO SQL begin


declare `out_city_id` bigint default 0;


if `in_name` = '' or `in_name` is null then
return null;
end if;


select `city`.`city_id` into `out_city_id`
from `city` where `city`.`name` like `in_name` and (`city`.`state_id` = `in_state_id` or `in_state_id` = '' or `in_state_id` = 0 or `in_state_id` is null) and `city`.`country_id` = `in_country_id` limit 0,1;


if `out_city_id` is null or `out_city_id` = 0 then
INSERT INTO `city` 
(`name`, `state_id`, `country_id`) values
(`in_name`, `in_state_id`, `in_country_id`);
select last_insert_id() into `out_city_id`;
end if;


return `out_city_id`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `country_id_to_name` (`cid` VARCHAR(5)) RETURNS VARCHAR(100) CHARSET latin1 NO SQL begin
declare `cname` varchar(100);


select `country`.`name` into `cname` from `country` where `country`.`country_id` like `cid` limit 0,1;
return `cname`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_class_id_from_name` (`in_class` VARCHAR(100), `in_school` BIGINT) RETURNS BIGINT(20) NO SQL begin
declare `out_id` bigint default null;
select `edu_class`.`class_id` into `out_id`
from `edu_class`
where `edu_class`.`name` like `in_class`
and `edu_class`.`school_id` = `in_school`
limit 0, 1;


if `out_id` is null then
INSERT INTO `edu_class` 
(`school_id`, `name`, `time_create`, `time_edit`, `active`) values
(`in_school`, `in_class`, now(), now(), '1');
select last_insert_id() into `out_id`;
end if;




return `out_id`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_last_order_image` (`id` BIGINT) RETURNS INT(11) NO SQL begin
declare `last_order` int(11) default 0;
SELECT `post_attachment`.`order` into `last_order` FROM `post_attachment` WHERE `post_attachment`.`post_id` = `id` order by `post_attachment`.`order` desc limit 0,1 ;
if `last_order` is null then
set `last_order` = 0;
end if;
return `last_order`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_school_id_from_name` (`in_school` VARCHAR(100)) RETURNS BIGINT(20) NO SQL begin
declare `out_id` bigint default null;
select `edu_school`.`school_id` into `out_id`
from `edu_school`
where `edu_school`.`name` like `in_school`
limit 0, 1;
return `out_id`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `get_school_program_id_from_name` (`in_school_program` VARCHAR(100), `in_school` BIGINT) RETURNS BIGINT(20) NO SQL begin
declare `out_id` bigint default null;
select `edu_school_program`.`school_program_id` into `out_id`
from `edu_school_program`
where `edu_school_program`.`name` like `in_school_program`
and `edu_school_program`.`school_id` = `in_school`
limit 0, 1;


if `out_id` is null then
INSERT INTO `edu_school_program` 
(`school_id`, `name`, `time_create`, `time_edit`, `active`) values
(`in_school`, `in_school_program`, now(), now(), '1');
select last_insert_id() into `out_id`;
end if;




return `out_id`;
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `state_id_to_name` (`in_state_id` BIGINT) RETURNS VARCHAR(100) CHARSET latin1 NO SQL begin


declare `out_name` varchar(100) default null;
select `state`.`name` into `out_name` from `state` where `state`.`state_id` = `in_state_id`;
return `out_name`;


end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `state_name_to_id` (`in_name` VARCHAR(100), `in_country_id` VARCHAR(3)) RETURNS BIGINT(20) NO SQL begin


declare `out_state_id` bigint default 0;


if `in_name` = '' or `in_name` is null then
return null;
end if;


select `state`.`state_id` into `out_state_id`
from `state` where `state`.`name` like `in_name` and `state`.`country_id` = `in_country_id` limit 0,1;


if `out_state_id` > 0 then
return `out_state_id`;
end if;


if `out_state_id` is null or `out_state_id` = 0 then
INSERT INTO `state` 
(`name`, `country_id`) values
(`in_name`, `in_country_id`);
select last_insert_id() into `out_state_id`;
end if;


return `out_state_id`;
end$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `city`
--

CREATE TABLE `city` (
  `city_id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `country_id` varchar(3) DEFAULT NULL,
  `state_id` bigint(20) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `verify` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `city`
--

INSERT INTO `city` (`city_id`, `name`, `country_id`, `state_id`, `type`, `verify`, `active`) VALUES
(21465, 'Denpasar', 'ID', 1667, '261', 1, 1),
(21466, 'Kabupaten Karangasem', 'ID', 1667, '211', 1, 1),
(21467, 'Kabupaten Klungkung', 'ID', 1667, '211', 1, 1),
(21468, 'Kuta', 'ID', 1667, '', 0, 0),
(21469, 'Negara', 'ID', 1667, '', 0, 0),
(21470, 'Singaraja', 'ID', 1667, '', 0, 0),
(21471, 'Kabupaten Tabanan', 'ID', 1667, '211', 1, 1),
(21472, 'Ubud', 'ID', 1667, '', 0, 0),
(21473, 'Manggar', 'ID', 1668, '', 0, 0),
(21474, 'Mentok', 'ID', 1668, '', 0, 0),
(21475, 'Pangkal Pinang', 'ID', 1668, '261', 1, 1),
(21476, 'Sungai Liat', 'ID', 1668, '', 0, 0),
(21477, 'Tanjung Pandan', 'ID', 1668, '', 0, 0),
(21478, 'Toboali-Rias', 'ID', 1668, '', 0, 0),
(21479, 'Cikupa', 'ID', 1669, '', 0, 0),
(21480, 'Cilegon', 'ID', 1669, '261', 1, 1),
(21481, 'Ciputat', 'ID', 1669, '', 0, 0),
(21482, 'Curug', 'ID', 1669, '', 0, 0),
(21483, 'Kresek', 'ID', 1669, '', 0, 0),
(21484, 'Labuhan', 'ID', 1669, '', 0, 0),
(21485, 'Pandegelang', 'ID', 1669, '', 0, 0),
(21486, 'Pondok Aren', 'ID', 1669, '', 0, 0),
(21487, 'Rangkasbitung', 'ID', 1669, '', 0, 0),
(21488, 'Serang', 'ID', 1669, '261', 1, 1),
(21489, 'Serpong', 'ID', 1669, '', 0, 0),
(21490, 'Tangerang', 'ID', 1669, '261', 1, 1),
(21491, 'Teluknaga', 'ID', 1669, '', 0, 0),
(21492, 'Bengkulu', 'ID', 1670, '261', 1, 1),
(21493, 'Curup', 'ID', 1670, '', 0, 0),
(21494, 'Gandaria', 'ID', 1671, '261', 1, 1),
(21495, 'Gorontalo', 'ID', 1672, '261', 1, 1),
(21496, 'Cengkareng', 'ID', 1673, '261', 1, 1),
(21497, 'Jakarta', 'ID', 1673, '261', 1, 1),
(21498, 'Jambi', 'ID', 1674, '261', 1, 1),
(21499, 'Kuala Tungkal', 'ID', 1674, '', 0, 0),
(21500, 'Simpang', 'ID', 1674, '', 0, 0),
(21501, 'Sungaipenuh', 'ID', 1674, '', 0, 0),
(21502, 'Kendal', 'ID', 1682, '261', 1, 1),
(21503, 'Bandar Lampung', 'ID', 1683, '261', 1, 1),
(21504, 'Kota Bumi', 'ID', 1683, '', 0, 0),
(21505, 'Metro', 'ID', 1683, '261', 1, 1),
(21506, 'Pringsewu', 'ID', 1683, '', 0, 0),
(21507, 'Terbanggi Besar', 'ID', 1683, '', 0, 0),
(21508, 'Amahai', 'ID', 1684, '', 0, 0),
(21509, 'Ambon', 'ID', 1684, '261', 1, 1),
(21510, 'Tual', 'ID', 1684, '261', 1, 1),
(21511, 'Ternate', 'ID', 1685, '261', 1, 1),
(21512, 'Tidore Kepulauan', 'ID', 1685, '261', 1, 1),
(21514, 'Aberpura', 'ID', 1688, '', 0, 0),
(21515, 'Biak', 'ID', 1688, '', 0, 0),
(21516, 'Jaya Pura', 'ID', 1688, '261', 1, 1),
(21517, 'Manokwari', 'ID', 1688, '', 0, 0),
(21518, 'Kabupaten Merauke', 'ID', 1688, '211', 1, 1),
(21519, 'Sorong', 'ID', 1688, '', 0, 0),
(21520, 'Balaipungut', 'ID', 1689, '261', 1, 1),
(21521, 'Kabupaten Bengkalis', 'ID', 1689, '211', 1, 1),
(21522, 'Dumai', 'ID', 1689, '261', 1, 1),
(21523, 'Duri', 'ID', 1689, '', 0, 0),
(21524, 'Pekan Baru', 'ID', 1689, '261', 1, 1),
(21525, 'Selatpanjang', 'ID', 1689, '', 0, 0),
(21526, 'Tanjung Balai-Meral', 'ID', 1689, '', 0, 0),
(21527, 'Tembilahan', 'ID', 1689, '', 0, 0),
(21528, 'Kabupaten Bintan', 'ID', 1690, '211', 1, 1),
(21529, 'Kabupaten Karimun', 'ID', 1690, '211', 1, 1),
(21530, 'Kabupaten Kepulauan Anambas', 'ID', 1690, '211', 1, 1),
(21531, 'Kabupaten Lingga', 'ID', 1690, '211', 1, 1),
(21532, 'Kabupaten Natuna', 'ID', 1690, '211', 1, 1),
(21533, 'Batam', 'ID', 1690, '261', 1, 1),
(21534, 'Tanjung Pinang', 'ID', 1690, '261', 1, 1),
(21536, 'Solo', 'ID', 1691, '261', 1, 1),
(21537, 'Bambanglipuro', 'ID', 1699, '', 0, 0),
(21538, 'Banguntapan', 'ID', 1699, '', 0, 0),
(21539, 'Kabupaten Bantul', 'ID', 1699, '211', 1, 1),
(21540, 'Kabupaten Gunungkidul', 'ID', 1699, '211', 1, 1),
(21541, 'Gamping', 'ID', 1699, '', 0, 0),
(21542, 'Godean', 'ID', 1699, '', 0, 0),
(21543, 'Jetis', 'ID', 1699, '', 0, 0),
(21544, 'Kasihan', 'ID', 1699, '', 0, 0),
(21545, 'Ngaglik', 'ID', 1699, '', 0, 0),
(21546, 'Pandak', 'ID', 1699, '', 0, 0),
(21547, 'Pundong', 'ID', 1699, '', 0, 0),
(21548, 'Sewon', 'ID', 1699, '', 0, 0),
(21549, 'Seyegan', 'ID', 1699, '', 0, 0),
(21550, 'Kabupaten Sleman', 'ID', 1699, '211', 1, 1),
(21551, 'Srandakan', 'ID', 1699, '', 0, 0),
(21552, 'Wonosari', 'ID', 1699, '', 0, 0),
(21553, 'Yogyakarta', 'ID', 1699, '261', 1, 1),
(48315, 'Muara Bulian', 'ID', 1674, '', 0, 0),
(48321, 'Negare', 'ID', 1667, '', 0, 0),
(48322, 'Jakarta Selatan', 'ID', 4242, '261', 1, 1),
(48323, 'Jakarta Pusat', 'ID', 4242, '261', 1, 1),
(48324, 'Jakarta Barat', 'ID', 4242, '261', 1, 1),
(48325, 'Jakarta Timur', 'ID', 4242, '261', 1, 1),
(48326, 'Kabupaten Administrasi Kepulau', 'ID', 4242, '211', 1, 1),
(48327, 'Jakarta Utara', 'ID', 4242, '261', 1, 1),
(48339, 'Tomohon', 'ID', 1695, '261', 1, 1),
(48341, 'Palembang', 'ID', 1697, '261', 1, 1),
(48342, 'Surakarta', 'ID', 1676, '261', 1, 1),
(48343, 'Purwakarta', 'ID', 1675, '', 0, 0),
(48344, 'Bandung', 'ID', 1675, '261', 1, 1),
(48345, 'Kabupaten Lumajang', 'ID', 1677, '211', 1, 1),
(48366, 'Kabupaten Banyuasin', 'ID', 1697, '211', 1, 1),
(48367, 'Kabupaten Empat Lawang', 'ID', 1697, '211', 1, 1),
(48368, 'Kabupaten Lahat', 'ID', 1697, '211', 1, 1),
(48369, 'Kabupaten Muara Enim', 'ID', 1697, '211', 1, 1),
(48370, 'Kabupaten Musi Banyuasin', 'ID', 1697, '211', 1, 1),
(48371, 'Kabupaten Musi Rawas', 'ID', 1697, '211', 1, 1),
(48372, 'Kabupaten Musi Rawas Utara', 'ID', 1697, '211', 1, 1),
(48373, 'Kabupaten Ogan Ilir', 'ID', 1697, '211', 1, 1),
(48374, 'Kabupaten Ogan Komering Ilir', 'ID', 1697, '211', 1, 1),
(48375, 'Kabupaten Ogan Komering Ulu', 'ID', 1697, '211', 1, 1),
(48376, 'Kabupaten Ogan Komering Ulu Se', 'ID', 1697, '211', 1, 1),
(48377, 'Kabupaten Ogan Komering Ulu Ti', 'ID', 1697, '211', 1, 1),
(48378, 'Kabupaten Penukal Abab Lematan', 'ID', 1697, '211', 1, 1),
(48379, 'Lubuklinggau', 'ID', 1697, '261', 1, 1),
(48380, 'Pagar Alam', 'ID', 1697, '261', 1, 1),
(48381, 'Prabumulih', 'ID', 1697, '261', 1, 1),
(48382, 'Kabupaten Asahan', 'ID', 1698, '211', 1, 1),
(48383, 'Kabupaten Batubara', 'ID', 1698, '211', 1, 1),
(48384, 'Kabupaten Dairi', 'ID', 1698, '211', 1, 1),
(48385, 'Kabupaten Deli Serdang', 'ID', 1698, '211', 1, 1),
(48386, 'Kabupaten Humbang Hasundutan', 'ID', 1698, '211', 1, 1),
(48387, 'Kabupaten Karo', 'ID', 1698, '211', 1, 1),
(48388, 'Kabupaten Labuhanbatu', 'ID', 1698, '211', 1, 1),
(48389, 'Kabupaten Labuhanbatu Selatan', 'ID', 1698, '211', 1, 1),
(48390, 'Kabupaten Labuhanbatu Utara', 'ID', 1698, '211', 1, 1),
(48391, 'Kabupaten Langkat', 'ID', 1698, '211', 1, 1),
(48392, 'Kabupaten Mandailing Natal', 'ID', 1698, '211', 1, 1),
(48393, 'Kabupaten Nias', 'ID', 1698, '211', 1, 1),
(48394, 'Kabupaten Nias Barat', 'ID', 1698, '211', 1, 1),
(48395, 'Kabupaten Nias Selatan', 'ID', 1698, '211', 1, 1),
(48396, 'Kabupaten Nias Utara', 'ID', 1698, '211', 1, 1),
(48397, 'Kabupaten Padang Lawas', 'ID', 1698, '211', 1, 1),
(48398, 'Kabupaten Padang Lawas Utara', 'ID', 1698, '211', 1, 1),
(48399, 'Kabupaten Pakpak Bharat', 'ID', 1698, '211', 1, 1),
(48400, 'Kabupaten Samosir', 'ID', 1698, '211', 1, 1),
(48401, 'Kabupaten Serdang Bedagai', 'ID', 1698, '211', 1, 1),
(48402, 'Kabupaten Simalungun', 'ID', 1698, '211', 1, 1),
(48403, 'Kabupaten Tapanuli Selatan', 'ID', 1698, '211', 1, 1),
(48404, 'Kabupaten Tapanuli Tengah', 'ID', 1698, '211', 1, 1),
(48405, 'Kabupaten Tapanuli Utara', 'ID', 1698, '211', 1, 1),
(48406, 'Kabupaten Toba Samosir', 'ID', 1698, '211', 1, 1),
(48407, 'Binjai', 'ID', 1698, '261', 1, 1),
(48408, 'Gunungsitoli', 'ID', 1698, '261', 1, 1),
(48409, 'Medan', 'ID', 1698, '261', 1, 1),
(48410, 'Padangsidempuan', 'ID', 1698, '261', 1, 1),
(48411, 'Pematangsiantar', 'ID', 1698, '261', 1, 1),
(48412, 'Sibolga', 'ID', 1698, '261', 1, 1),
(48413, 'Tanjungbalai', 'ID', 1698, '261', 1, 1),
(48414, 'Tebing Tinggi', 'ID', 1698, '261', 1, 1),
(48415, 'Kabupaten Indragiri Hilir', 'ID', 1689, '211', 1, 1),
(48416, 'Kabupaten Indragiri Hulu', 'ID', 1689, '211', 1, 1),
(48417, 'Kabupaten Kampar', 'ID', 1689, '211', 1, 1),
(48418, 'Kabupaten Kepulauan Meranti', 'ID', 1689, '211', 1, 1),
(48419, 'Kabupaten Kuantan Singingi', 'ID', 1689, '211', 1, 1),
(48420, 'Kabupaten Pelalawan', 'ID', 1689, '211', 1, 1),
(48421, 'Kabupaten Rokan Hilir', 'ID', 1689, '211', 1, 1),
(48422, 'Kabupaten Rokan Hulu', 'ID', 1689, '211', 1, 1),
(48423, 'Kabupaten Siak', 'ID', 1689, '211', 1, 1),
(48424, 'Kabupaten Agam', 'ID', 1696, '211', 1, 1),
(48425, 'Kabupaten Dharmasraya', 'ID', 1696, '211', 1, 1),
(48426, 'Kabupaten Kepulauan Mentawai', 'ID', 1696, '211', 1, 1),
(48427, 'Kabupaten Lima Puluh Kota', 'ID', 1696, '211', 1, 1),
(48428, 'Kabupaten Padang Pariaman', 'ID', 1696, '211', 1, 1),
(48429, 'Kabupaten Pasaman', 'ID', 1696, '211', 1, 1),
(48430, 'Kabupaten Pasaman Barat', 'ID', 1696, '211', 1, 1),
(48431, 'Kabupaten Pesisir Selatan', 'ID', 1696, '211', 1, 1),
(48432, 'Kabupaten Sijunjung', 'ID', 1696, '211', 1, 1),
(48433, 'Kabupaten Solok', 'ID', 1696, '211', 1, 1),
(48434, 'Kabupaten Solok Selatan', 'ID', 1696, '211', 1, 1),
(48435, 'Kabupaten Tanah Datar', 'ID', 1696, '211', 1, 1),
(48436, 'Bukittinggi', 'ID', 1696, '261', 1, 1),
(48437, 'Padang', 'ID', 1696, '261', 1, 1),
(48438, 'Padangpanjang', 'ID', 1696, '261', 1, 1),
(48439, 'Pariaman', 'ID', 1696, '261', 1, 1),
(48440, 'Payakumbuh', 'ID', 1696, '261', 1, 1),
(48441, 'Sawahlunto', 'ID', 1696, '261', 1, 1),
(48442, 'Solok', 'ID', 1696, '261', 1, 1),
(48443, 'Kabupaten Batanghari', 'ID', 1674, '211', 1, 1),
(48444, 'Kabupaten Bungo', 'ID', 1674, '211', 1, 1),
(48445, 'Kabupaten Kerinci', 'ID', 1674, '211', 1, 1),
(48446, 'Kabupaten Merangin', 'ID', 1674, '211', 1, 1),
(48447, 'Kabupaten Muaro Jambi', 'ID', 1674, '211', 1, 1),
(48448, 'Kabupaten Sarolangun', 'ID', 1674, '211', 1, 1),
(48449, 'Kabupaten Tanjung Jabung Barat', 'ID', 1674, '211', 1, 1),
(48450, 'Kabupaten Tanjung Jabung Timur', 'ID', 1674, '211', 1, 1),
(48451, 'Kabupaten Tebo', 'ID', 1674, '211', 1, 1),
(48452, 'Sungai Penuh', 'ID', 1674, '261', 1, 1),
(48453, 'Kabupaten Bengkulu Selatan', 'ID', 1670, '211', 1, 1),
(48454, 'Kabupaten Bengkulu Tengah', 'ID', 1670, '211', 1, 1),
(48455, 'Kabupaten Bengkulu Utara', 'ID', 1670, '211', 1, 1),
(48456, 'Kabupaten Kaur', 'ID', 1670, '211', 1, 1),
(48457, 'Kabupaten Kepahiang', 'ID', 1670, '211', 1, 1),
(48458, 'Kabupaten Lebong', 'ID', 1670, '211', 1, 1),
(48459, 'Kabupaten Mukomuko', 'ID', 1670, '211', 1, 1),
(48460, 'Kabupaten Rejang Lebong', 'ID', 1670, '211', 1, 1),
(48461, 'Kabupaten Seluma', 'ID', 1670, '211', 1, 1),
(48462, 'Kabupaten Lampung Barat', 'ID', 1683, '211', 1, 1),
(48463, 'Kabupaten Lampung Selatan', 'ID', 1683, '211', 1, 1),
(48464, 'Kabupaten Lampung Tengah', 'ID', 1683, '211', 1, 1),
(48465, 'Kabupaten Lampung Timur', 'ID', 1683, '211', 1, 1),
(48466, 'Kabupaten Lampung Utara', 'ID', 1683, '211', 1, 1),
(48467, 'Kabupaten Mesuji', 'ID', 1683, '211', 1, 1),
(48468, 'Kabupaten Pesawaran', 'ID', 1683, '211', 1, 1),
(48469, 'Kabupaten Pesisir Barat', 'ID', 1683, '211', 1, 1),
(48470, 'Kabupaten Pringsewu', 'ID', 1683, '211', 1, 1),
(48471, 'Kabupaten Tanggamus', 'ID', 1683, '211', 1, 1),
(48472, 'Kabupaten Tulang Bawang', 'ID', 1683, '211', 1, 1),
(48473, 'Kabupaten Tulang Bawang Barat', 'ID', 1683, '211', 1, 1),
(48474, 'Kabupaten Way Kanan', 'ID', 1683, '211', 1, 1),
(48475, 'Kabupaten Bangka', 'ID', 1668, '211', 1, 1),
(48476, 'Kabupaten Bangka Barat', 'ID', 1668, '211', 1, 1),
(48477, 'Kabupaten Bangka Selatan', 'ID', 1668, '211', 1, 1),
(48478, 'Kabupaten Bangka Tengah', 'ID', 1668, '211', 1, 1),
(48479, 'Kabupaten Belitung', 'ID', 1668, '211', 1, 1),
(48480, 'Kabupaten Belitung Timur', 'ID', 1668, '211', 1, 1),
(48481, 'Kabupaten Lebak', 'ID', 1669, '211', 1, 1),
(48482, 'Kabupaten Pandeglang', 'ID', 1669, '211', 1, 1),
(48483, 'Kabupaten Serang', 'ID', 1669, '211', 1, 1),
(48484, 'Kabupaten Tangerang', 'ID', 1669, '211', 1, 1),
(48485, 'Kabupaten Bandung', 'ID', 1675, '211', 1, 1),
(48486, 'Kabupaten Bandung Barat', 'ID', 1675, '211', 1, 1),
(48487, 'Kabupaten Bekasi', 'ID', 1675, '211', 1, 1),
(48488, 'Kabupaten Bogor', 'ID', 1675, '211', 1, 1),
(48489, 'Kabupaten Ciamis', 'ID', 1675, '211', 1, 1),
(48490, 'Kabupaten Cianjur', 'ID', 1675, '211', 1, 1),
(48491, 'Kabupaten Cirebon', 'ID', 1675, '211', 1, 1),
(48492, 'Kabupaten Garut', 'ID', 1675, '211', 1, 1),
(48493, 'Kabupaten Indramayu', 'ID', 1675, '211', 1, 1),
(48494, 'Kabupaten Karawang', 'ID', 1675, '211', 1, 1),
(48495, 'Kabupaten Kuningan', 'ID', 1675, '211', 1, 1),
(48496, 'Kabupaten Majalengka', 'ID', 1675, '211', 1, 1),
(48497, 'Kabupaten Pangandaran', 'ID', 1675, '211', 1, 1),
(48498, 'Kabupaten Purwakarta', 'ID', 1675, '211', 1, 1),
(48499, 'Kabupaten Subang', 'ID', 1675, '211', 1, 1),
(48500, 'Kabupaten Sukabumi', 'ID', 1675, '211', 1, 1),
(48501, 'Kabupaten Sumedang', 'ID', 1675, '211', 1, 1),
(48502, 'Kabupaten Tasikmalaya', 'ID', 1675, '211', 1, 1),
(48503, 'Banjar', 'ID', 1675, '261', 1, 1),
(48504, 'Bekasi', 'ID', 1675, '261', 1, 1),
(48505, 'Bogor', 'ID', 1675, '261', 1, 1),
(48506, 'Cimahi', 'ID', 1675, '261', 1, 1),
(48507, 'Cirebon', 'ID', 1675, '261', 1, 1),
(48508, 'Depok', 'ID', 1675, '261', 1, 1),
(48509, 'Sukabumi', 'ID', 1675, '261', 1, 1),
(48510, 'Tasikmalaya', 'ID', 1675, '261', 1, 1),
(48511, 'Kabupaten Banjarnegara', 'ID', 1676, '211', 1, 1),
(48512, 'Kabupaten Banyumas', 'ID', 1676, '211', 1, 1),
(48513, 'Kabupaten Batang', 'ID', 1676, '211', 1, 1),
(48514, 'Kabupaten Blora', 'ID', 1676, '211', 1, 1),
(48515, 'Kabupaten Boyolali', 'ID', 1676, '211', 1, 1),
(48516, 'Kabupaten Brebes', 'ID', 1676, '211', 1, 1),
(48517, 'Kabupaten Cilacap', 'ID', 1676, '211', 1, 1),
(48518, 'Kabupaten Demak', 'ID', 1676, '211', 1, 1),
(48519, 'Kabupaten Grobogan', 'ID', 1676, '211', 1, 1),
(48520, 'Kabupaten Jepara', 'ID', 1676, '211', 1, 1),
(48521, 'Kabupaten Karanganyar', 'ID', 1676, '211', 1, 1),
(48522, 'Kabupaten Kebumen', 'ID', 1676, '211', 1, 1),
(48523, 'Kabupaten Kendal', 'ID', 1676, '211', 1, 1),
(48524, 'Kabupaten Klaten', 'ID', 1676, '211', 1, 1),
(48525, 'Kabupaten Kudus', 'ID', 1676, '211', 1, 1),
(48526, 'Kabupaten Magelang', 'ID', 1676, '211', 1, 1),
(48527, 'Kabupaten Pati', 'ID', 1676, '211', 1, 1),
(48528, 'Kabupaten Pekalongan', 'ID', 1676, '211', 1, 1),
(48529, 'Kabupaten Pemalang', 'ID', 1676, '211', 1, 1),
(48530, 'Kabupaten Purbalingga', 'ID', 1676, '211', 1, 1),
(48531, 'Kabupaten Purworejo', 'ID', 1676, '211', 1, 1),
(48532, 'Kabupaten Rembang', 'ID', 1676, '211', 1, 1),
(48533, 'Kabupaten Semarang', 'ID', 1676, '211', 1, 1),
(48534, 'Kabupaten Sragen', 'ID', 1676, '211', 1, 1),
(48535, 'Kabupaten Sukoharjo', 'ID', 1676, '211', 1, 1),
(48536, 'Kabupaten Tegal', 'ID', 1676, '211', 1, 1),
(48537, 'Kabupaten Temanggung', 'ID', 1676, '211', 1, 1),
(48538, 'Kabupaten Wonogiri', 'ID', 1676, '211', 1, 1),
(48539, 'Kabupaten Wonosobo', 'ID', 1676, '211', 1, 1),
(48540, 'Magelang', 'ID', 1676, '261', 1, 1),
(48541, 'Pekalongan', 'ID', 1676, '261', 1, 1),
(48542, 'Salatiga', 'ID', 1676, '261', 1, 1),
(48543, 'Semarang', 'ID', 1676, '261', 1, 1),
(48544, 'Tegal', 'ID', 1676, '261', 1, 1),
(48545, 'Kabupaten Kulon Progo', 'ID', 1699, '211', 1, 1),
(48546, 'Kabupaten Bangkalan', 'ID', 1677, '211', 1, 1),
(48547, 'Kabupaten Banyuwangi', 'ID', 1677, '211', 1, 1),
(48548, 'Kabupaten Blitar', 'ID', 1677, '211', 1, 1),
(48549, 'Kabupaten Bojonegoro', 'ID', 1677, '211', 1, 1),
(48550, 'Kabupaten Bondowoso', 'ID', 1677, '211', 1, 1),
(48551, 'Kabupaten Gresik', 'ID', 1677, '211', 1, 1),
(48552, 'Kabupaten Jember', 'ID', 1677, '211', 1, 1),
(48553, 'Kabupaten Jombang', 'ID', 1677, '211', 1, 1),
(48554, 'Kabupaten Kediri', 'ID', 1677, '211', 1, 1),
(48555, 'Kabupaten Lamongan', 'ID', 1677, '211', 1, 1),
(48556, 'Kabupaten Madiun', 'ID', 1677, '211', 1, 1),
(48557, 'Kabupaten Magetan', 'ID', 1677, '211', 1, 1),
(48558, 'Kabupaten Malang', 'ID', 1677, '211', 1, 1),
(48559, 'Kabupaten Mojokerto', 'ID', 1677, '211', 1, 1),
(48560, 'Kabupaten Nganjuk', 'ID', 1677, '211', 1, 1),
(48561, 'Kabupaten Ngawi', 'ID', 1677, '211', 1, 1),
(48562, 'Kabupaten Pacitan', 'ID', 1677, '211', 1, 1),
(48563, 'Kabupaten Pamekasan', 'ID', 1677, '211', 1, 1),
(48564, 'Kabupaten Pasuruan', 'ID', 1677, '211', 1, 1),
(48565, 'Kabupaten Ponorogo', 'ID', 1677, '211', 1, 1),
(48566, 'Kabupaten Probolinggo', 'ID', 1677, '211', 1, 1),
(48567, 'Kabupaten Sampang', 'ID', 1677, '211', 1, 1),
(48568, 'Kabupaten Sidoarjo', 'ID', 1677, '211', 1, 1),
(48569, 'Kabupaten Situbondo', 'ID', 1677, '211', 1, 1),
(48570, 'Kabupaten Sumenep', 'ID', 1677, '211', 1, 1),
(48571, 'Kabupaten Trenggalek', 'ID', 1677, '211', 1, 1),
(48572, 'Kabupaten Tuban', 'ID', 1677, '211', 1, 1),
(48573, 'Kabupaten Tulungagung', 'ID', 1677, '211', 1, 1),
(48574, 'Batu', 'ID', 1677, '261', 1, 1),
(48575, 'Blitar', 'ID', 1677, '261', 1, 1),
(48576, 'Kediri', 'ID', 1677, '261', 1, 1),
(48577, 'Madiun', 'ID', 1677, '261', 1, 1),
(48578, 'Malang', 'ID', 1677, '261', 1, 1),
(48579, 'Mojokerto', 'ID', 1677, '261', 1, 1),
(48580, 'Pasuruan', 'ID', 1677, '261', 1, 1),
(48581, 'Probolinggo', 'ID', 1677, '261', 1, 1),
(48582, 'Surabaya', 'ID', 1677, '261', 1, 1),
(48583, 'Kabupaten Bengkayang', 'ID', 1678, '211', 1, 1),
(48584, 'Kabupaten Kapuas Hulu', 'ID', 1678, '211', 1, 1),
(48585, 'Kabupaten Kayong Utara', 'ID', 1678, '211', 1, 1),
(48586, 'Kabupaten Ketapang', 'ID', 1678, '211', 1, 1),
(48587, 'Kabupaten Kubu Raya', 'ID', 1678, '211', 1, 1),
(48588, 'Kabupaten Landak', 'ID', 1678, '211', 1, 1),
(48589, 'Kabupaten Melawi', 'ID', 1678, '211', 1, 1),
(48590, 'Kabupaten Mempawah', 'ID', 1678, '211', 1, 1),
(48591, 'Kabupaten Sambas', 'ID', 1678, '211', 1, 1),
(48592, 'Kabupaten Sanggau', 'ID', 1678, '211', 1, 1),
(48593, 'Kabupaten Sekadau', 'ID', 1678, '211', 1, 1),
(48594, 'Kabupaten Sintang', 'ID', 1678, '211', 1, 1),
(48595, 'Pontianak', 'ID', 1678, '261', 1, 1),
(48596, 'Singkawang', 'ID', 1678, '261', 1, 1),
(48597, 'Kabupaten Balangan', 'ID', 1679, '211', 1, 1),
(48598, 'Kabupaten Banjar', 'ID', 1679, '211', 1, 1),
(48599, 'Kabupaten Barito Kuala', 'ID', 1679, '211', 1, 1),
(48600, 'Kabupaten Hulu Sungai Selatan', 'ID', 1679, '211', 1, 1),
(48601, 'Kabupaten Hulu Sungai Tengah', 'ID', 1679, '211', 1, 1),
(48602, 'Kabupaten Hulu Sungai Utara', 'ID', 1679, '211', 1, 1),
(48603, 'Kabupaten Kotabaru', 'ID', 1679, '211', 1, 1),
(48604, 'Kabupaten Tabalong', 'ID', 1679, '211', 1, 1),
(48605, 'Kabupaten Tanah Bumbu', 'ID', 1679, '211', 1, 1),
(48606, 'Kabupaten Tanah Laut', 'ID', 1679, '211', 1, 1),
(48607, 'Kabupaten Tapin', 'ID', 1679, '211', 1, 1),
(48608, 'Banjarbaru', 'ID', 1679, '261', 1, 1),
(48609, 'Banjarmasin', 'ID', 1679, '261', 1, 1),
(48610, 'Kabupaten Barito Selatan', 'ID', 1680, '211', 1, 1),
(48611, 'Kabupaten Barito Timur', 'ID', 1680, '211', 1, 1),
(48612, 'Kabupaten Barito Utara', 'ID', 1680, '211', 1, 1),
(48613, 'Kabupaten Gunung Mas', 'ID', 1680, '211', 1, 1),
(48614, 'Kabupaten Kapuas', 'ID', 1680, '211', 1, 1),
(48615, 'Kabupaten Katingan', 'ID', 1680, '211', 1, 1),
(48616, 'Kabupaten Kotawaringin Barat', 'ID', 1680, '211', 1, 1),
(48617, 'Kabupaten Kotawaringin Timur', 'ID', 1680, '211', 1, 1),
(48618, 'Kabupaten Lamandau', 'ID', 1680, '211', 1, 1),
(48619, 'Kabupaten Murung Raya', 'ID', 1680, '211', 1, 1),
(48620, 'Kabupaten Pulang Pisau', 'ID', 1680, '211', 1, 1),
(48621, 'Kabupaten Sukamara', 'ID', 1680, '211', 1, 1),
(48622, 'Kabupaten Seruyan', 'ID', 1680, '211', 1, 1),
(48623, 'Palangka Raya', 'ID', 1680, '261', 1, 1),
(48624, 'Kabupaten Berau', 'ID', 1681, '211', 1, 1),
(48625, 'Kabupaten Kutai Barat', 'ID', 1681, '211', 1, 1),
(48626, 'Kabupaten Kutai Kartanegara', 'ID', 1681, '211', 1, 1),
(48627, 'Kabupaten Kutai Timur', 'ID', 1681, '211', 1, 1),
(48628, 'Kabupaten Mahakam Ulu', 'ID', 1681, '211', 1, 1),
(48629, 'Kabupaten Paser', 'ID', 1681, '211', 1, 1),
(48630, 'Kabupaten Penajam Paser Utara', 'ID', 1681, '211', 1, 1),
(48631, 'Balikpapan', 'ID', 1681, '261', 1, 1),
(48632, 'Bontang', 'ID', 1681, '261', 1, 1),
(48633, 'Samarinda', 'ID', 1681, '261', 1, 1),
(48634, 'Kabupaten Majene', 'ID', 4243, '211', 1, 1),
(48635, 'Kabupaten Mamasa', 'ID', 4243, '211', 1, 1),
(48636, 'Kabupaten Mamuju', 'ID', 4243, '211', 1, 1),
(48637, 'Kabupaten Mamuju Tengah', 'ID', 4243, '211', 1, 1),
(48638, 'Kabupaten Mamuju Utara', 'ID', 4243, '211', 1, 1),
(48639, 'Kabupaten Polewali Mandar', 'ID', 4243, '211', 1, 1),
(48640, 'Kabupaten Banggai', 'ID', 1693, '211', 1, 1),
(48641, 'Kabupaten Banggai Kepulauan', 'ID', 1693, '211', 1, 1),
(48642, 'Kabupaten Banggai Laut', 'ID', 1693, '211', 1, 1),
(48643, 'Kabupaten Buol', 'ID', 1693, '211', 1, 1),
(48644, 'Kabupaten Donggala', 'ID', 1693, '211', 1, 1),
(48645, 'Kabupaten Morowali', 'ID', 1693, '211', 1, 1),
(48646, 'Kabupaten Morowali Utara', 'ID', 1693, '211', 1, 1),
(48647, 'Kabupaten Parigi Moutong', 'ID', 1693, '211', 1, 1),
(48648, 'Kabupaten Poso', 'ID', 1693, '211', 1, 1),
(48649, 'Kabupaten Sigi', 'ID', 1693, '211', 1, 1),
(48650, 'Kabupaten Tojo Una-Una', 'ID', 1693, '211', 1, 1),
(48651, 'Kabupaten Tolitoli', 'ID', 1693, '211', 1, 1),
(48652, 'Palu', 'ID', 1693, '261', 1, 1),
(48653, 'Kabupaten Bantaeng', 'ID', 1692, '211', 1, 1),
(48654, 'Kabupaten Barru', 'ID', 1692, '211', 1, 1),
(48655, 'Kabupaten Bone', 'ID', 1692, '211', 1, 1),
(48656, 'Kabupaten Bulukumba', 'ID', 1692, '211', 1, 1),
(48657, 'Kabupaten Enrekang', 'ID', 1692, '211', 1, 1),
(48658, 'Kabupaten Gowa', 'ID', 1692, '211', 1, 1),
(48659, 'Kabupaten Jeneponto', 'ID', 1692, '211', 1, 1),
(48660, 'Kabupaten Kepulauan Selayar', 'ID', 1692, '211', 1, 1),
(48661, 'Kabupaten Luwu', 'ID', 1692, '211', 1, 1),
(48662, 'Kabupaten Luwu Timur', 'ID', 1692, '211', 1, 1),
(48663, 'Kabupaten Luwu Utara', 'ID', 1692, '211', 1, 1),
(48664, 'Kabupaten Maros', 'ID', 1692, '211', 1, 1),
(48665, 'Kabupaten Pangkajene dan Kepul', 'ID', 1692, '211', 1, 1),
(48666, 'Kabupaten Pinrang', 'ID', 1692, '211', 1, 1),
(48667, 'Kabupaten Sidenreng Rappang', 'ID', 1692, '211', 1, 1),
(48668, 'Kabupaten Sinjai', 'ID', 1692, '211', 1, 1),
(48669, 'Kabupaten Soppeng', 'ID', 1692, '211', 1, 1),
(48670, 'Kabupaten Takalar', 'ID', 1692, '211', 1, 1),
(48671, 'Kabupaten Tana Toraja', 'ID', 1692, '211', 1, 1),
(48672, 'Kabupaten Toraja Utara', 'ID', 1692, '211', 1, 1),
(48673, 'Kabupaten Wajo', 'ID', 1692, '211', 1, 1),
(48674, 'Makassar', 'ID', 1692, '261', 1, 1),
(48675, 'Palopo', 'ID', 1692, '261', 1, 1),
(48676, 'Parepare', 'ID', 1692, '261', 1, 1),
(48677, 'Kabupaten Bombana', 'ID', 1694, '211', 1, 1),
(48678, 'Kabupaten Buton', 'ID', 1694, '211', 1, 1),
(48679, 'Kabupaten Buton Selatan', 'ID', 1694, '211', 1, 1),
(48680, 'Kabupaten Buton Tengah', 'ID', 1694, '211', 1, 1),
(48681, 'Kabupaten Buton Utara', 'ID', 1694, '211', 1, 1),
(48682, 'Kabupaten Kolaka', 'ID', 1694, '211', 1, 1),
(48683, 'Kabupaten Kolaka Timur', 'ID', 1694, '211', 1, 1),
(48684, 'Kabupaten Kolaka Utara', 'ID', 1694, '211', 1, 1),
(48685, 'Kabupaten Konawe', 'ID', 1694, '211', 1, 1),
(48686, 'Kabupaten Konawe Kepulauan', 'ID', 1694, '211', 1, 1),
(48687, 'Kabupaten Konawe Selatan', 'ID', 1694, '211', 1, 1),
(48688, 'Kabupaten Konawe Utara', 'ID', 1694, '211', 1, 1),
(48689, 'Kabupaten Muna', 'ID', 1694, '211', 1, 1),
(48690, 'Kabupaten Muna Barat', 'ID', 1694, '211', 1, 1),
(48691, 'Kabupaten Wakatobi', 'ID', 1694, '211', 1, 1),
(48692, 'Bau-Bau', 'ID', 1694, '261', 1, 1),
(48693, 'Kendari', 'ID', 1694, '261', 1, 1),
(48694, 'Kabupaten Boalemo', 'ID', 1672, '211', 1, 1),
(48695, 'Kabupaten Bone Bolango', 'ID', 1672, '211', 1, 1),
(48696, 'Kabupaten Gorontalo', 'ID', 1672, '211', 1, 1),
(48697, 'Kabupaten Gorontalo Utara', 'ID', 1672, '211', 1, 1),
(48698, 'Kabupaten Pohuwato', 'ID', 1672, '211', 1, 1),
(48699, 'Kabupaten Bima', 'ID', 1686, '211', 1, 1),
(48700, 'Kabupaten Dompu', 'ID', 1686, '211', 1, 1),
(48701, 'Kabupaten Lombok Barat', 'ID', 1686, '211', 1, 1),
(48702, 'Kabupaten Lombok Tengah', 'ID', 1686, '211', 1, 1),
(48703, 'Kabupaten Lombok Timur', 'ID', 1686, '211', 1, 1),
(48704, 'Kabupaten Lombok Utara', 'ID', 1686, '211', 1, 1),
(48705, 'Kabupaten Sumbawa', 'ID', 1686, '211', 1, 1),
(48706, 'Kabupaten Sumbawa Barat', 'ID', 1686, '211', 1, 1),
(48707, 'Bima', 'ID', 1686, '261', 1, 1),
(48708, 'Mataram', 'ID', 1686, '261', 1, 1),
(48709, 'Kabupaten Alor', 'ID', 1687, '211', 1, 1),
(48710, 'Kabupaten Belu', 'ID', 1687, '211', 1, 1),
(48711, 'Kabupaten Ende', 'ID', 1687, '211', 1, 1),
(48712, 'Kabupaten Flores Timur', 'ID', 1687, '211', 1, 1),
(48713, 'Kabupaten Kupang', 'ID', 1687, '211', 1, 1),
(48714, 'Kabupaten Lembata', 'ID', 1687, '211', 1, 1),
(48715, 'Kabupaten Malaka', 'ID', 1687, '211', 1, 1),
(48716, 'Kabupaten Manggarai', 'ID', 1687, '211', 1, 1),
(48717, 'Kabupaten Manggarai Barat', 'ID', 1687, '211', 1, 1),
(48718, 'Kabupaten Manggarai Timur', 'ID', 1687, '211', 1, 1),
(48719, 'Kabupaten Ngada', 'ID', 1687, '211', 1, 1),
(48720, 'Kabupaten Nagekeo', 'ID', 1687, '211', 1, 1),
(48721, 'Kabupaten Rote Ndao', 'ID', 1687, '211', 1, 1),
(48722, 'Kabupaten Sabu Raijua', 'ID', 1687, '211', 1, 1),
(48723, 'Kabupaten Sikka', 'ID', 1687, '211', 1, 1),
(48724, 'Kabupaten Sumba Barat', 'ID', 1687, '211', 1, 1),
(48725, 'Kabupaten Sumba Barat Daya', 'ID', 1687, '211', 1, 1),
(48726, 'Kabupaten Sumba Tengah', 'ID', 1687, '211', 1, 1),
(48727, 'Kabupaten Sumba Timur', 'ID', 1687, '211', 1, 1),
(48728, 'Kabupaten Timor Tengah Selatan', 'ID', 1687, '211', 1, 1),
(48729, 'Kabupaten Timor Tengah Utara', 'ID', 1687, '211', 1, 1),
(48730, 'Kupang', 'ID', 1687, '261', 1, 1),
(48731, 'Kabupaten Buru', 'ID', 1684, '211', 1, 1),
(48732, 'Kabupaten Buru Selatan', 'ID', 1684, '211', 1, 1),
(48733, 'Kabupaten Kepulauan Aru', 'ID', 1684, '211', 1, 1),
(48734, 'Kabupaten Maluku Barat Daya', 'ID', 1684, '211', 1, 1),
(48735, 'Kabupaten Maluku Tengah', 'ID', 1684, '211', 1, 1),
(48736, 'Kabupaten Maluku Tenggara', 'ID', 1684, '211', 1, 1),
(48737, 'Kabupaten Maluku Tenggara Bara', 'ID', 1684, '211', 1, 1),
(48738, 'Kabupaten Seram Bagian Barat', 'ID', 1684, '211', 1, 1),
(48739, 'Kabupaten Seram Bagian Timur', 'ID', 1684, '211', 1, 1),
(48740, 'Kabupaten Asmat', 'ID', 1688, '211', 1, 1),
(48741, 'Kabupaten Biak Numfor', 'ID', 1688, '211', 1, 1),
(48742, 'Kabupaten Boven Digoel', 'ID', 1688, '211', 1, 1),
(48743, 'Kabupaten Deiyai', 'ID', 1688, '211', 1, 1),
(48744, 'Kabupaten Dogiyai', 'ID', 1688, '211', 1, 1),
(48745, 'Kabupaten Intan Jaya', 'ID', 1688, '211', 1, 1),
(48746, 'Kabupaten Jayapura', 'ID', 1688, '211', 1, 1),
(48747, 'Kabupaten Jayawijaya', 'ID', 1688, '211', 1, 1),
(48748, 'Kabupaten Keerom', 'ID', 1688, '211', 1, 1),
(48749, 'Kabupaten Kepulauan Yapen', 'ID', 1688, '211', 1, 1),
(48750, 'Kabupaten Lanny Jaya', 'ID', 1688, '211', 1, 1),
(48751, 'Kabupaten Mamberamo Raya', 'ID', 1688, '211', 1, 1),
(48752, 'Kabupaten Mamberamo Tengah', 'ID', 1688, '211', 1, 1),
(48753, 'Kabupaten Mappi', 'ID', 1688, '211', 1, 1),
(48754, 'Kabupaten Mimika', 'ID', 1688, '211', 1, 1),
(48755, 'Kabupaten Nabire', 'ID', 1688, '211', 1, 1),
(48756, 'Kabupaten Nduga', 'ID', 1688, '211', 1, 1),
(48757, 'Kabupaten Paniai', 'ID', 1688, '211', 1, 1),
(48758, 'Kabupaten Pegunungan Bintang', 'ID', 1688, '211', 1, 1),
(48759, 'Kabupaten Puncak', 'ID', 1688, '211', 1, 1),
(48760, 'Kabupaten Puncak Jaya', 'ID', 1688, '211', 1, 1),
(48761, 'Kabupaten Sarmi', 'ID', 1688, '211', 1, 1),
(48762, 'Kabupaten Supiori', 'ID', 1688, '211', 1, 1),
(48763, 'Kabupaten Tolikara', 'ID', 1688, '211', 1, 1),
(48764, 'Kabupaten Waropen', 'ID', 1688, '211', 1, 1),
(48765, 'Kabupaten Yahukimo', 'ID', 1688, '211', 1, 1),
(48766, 'Kabupaten Yalimo', 'ID', 1688, '211', 1, 1),
(48767, 'Kabupaten Fakfak', 'ID', 4244, '211', 1, 1),
(48768, 'Kabupaten Kaimana', 'ID', 4244, '211', 1, 1),
(48769, 'Kabupaten Manokwari', 'ID', 4244, '211', 1, 1),
(48770, 'Kabupaten Manokwari Selatan', 'ID', 4244, '211', 1, 1),
(48771, 'Kabupaten Maybrat', 'ID', 4244, '211', 1, 1),
(48772, 'Kabupaten Pegunungan Arfak', 'ID', 4244, '211', 1, 1),
(48773, 'Kabupaten Raja Ampat', 'ID', 4244, '211', 1, 1),
(48774, 'Kabupaten Sorong', 'ID', 4244, '211', 1, 1),
(48775, 'Kabupaten Sorong Selatan', 'ID', 4244, '211', 1, 1),
(48776, 'Kabupaten Tambrauw', 'ID', 4244, '211', 1, 1),
(48777, 'Kabupaten Teluk Bintuni', 'ID', 4244, '211', 1, 1),
(48778, 'Kabupaten Teluk Wondama', 'ID', 4244, '211', 1, 1),
(48779, 'Sorong', 'ID', 4244, '261', 1, 1),
(48780, 'Kabupaten Aceh Barat', 'ID', 4246, '211', 1, 1),
(48781, 'Kabupaten Aceh Barat Daya', 'ID', 4246, '211', 1, 1),
(48782, 'Kabupaten Aceh Besar', 'ID', 4246, '211', 1, 1),
(48783, 'Kabupaten Aceh Jaya', 'ID', 4246, '211', 1, 1),
(48784, 'Kabupaten Aceh Selatan', 'ID', 4246, '211', 1, 1),
(48785, 'Kabupaten Aceh Singkil', 'ID', 4246, '211', 1, 1),
(48786, 'Kabupaten Aceh Tamiang', 'ID', 4246, '211', 1, 1),
(48787, 'Kabupaten Aceh Tengah', 'ID', 4246, '211', 1, 1),
(48788, 'Kabupaten Aceh Tenggara', 'ID', 4246, '211', 1, 1),
(48789, 'Kabupaten Aceh Timur', 'ID', 4246, '211', 1, 1),
(48790, 'Kabupaten Aceh Utara', 'ID', 4246, '211', 1, 1),
(48791, 'Kabupaten Bener Meriah', 'ID', 4246, '211', 1, 1),
(48792, 'Kabupaten Bireuen', 'ID', 4246, '211', 1, 1),
(48793, 'Kabupaten Gayo Lues', 'ID', 4246, '211', 1, 1),
(48794, 'Kabupaten Nagan Raya', 'ID', 4246, '211', 1, 1),
(48795, 'Kabupaten Pidie', 'ID', 4246, '211', 1, 1),
(48796, 'Kabupaten Pidie Jaya', 'ID', 4246, '211', 1, 1),
(48797, 'Kabupaten Simeulue', 'ID', 4246, '211', 1, 1),
(48798, 'Banda Aceh', 'ID', 4246, '261', 1, 1),
(48799, 'Langsa', 'ID', 4246, '261', 1, 1),
(48800, 'Lhokseumawe', 'ID', 4246, '261', 1, 1),
(48801, 'Sabang', 'ID', 4246, '261', 1, 1),
(48802, 'Subulussalam', 'ID', 4246, '261', 1, 1),
(48803, 'Kabupaten Buleleng', 'ID', 1667, '211', 1, 1),
(48804, 'Kabupaten Gianyar', 'ID', 1667, '211', 1, 1),
(48805, 'Kabupaten Jembrana', 'ID', 1667, '211', 1, 1),
(48806, 'Kabupaten Badung', 'ID', 1667, '211', 1, 1),
(48807, 'Kabupaten Bangli', 'ID', 1667, '211', 1, 1),
(48808, 'Kabupaten Bulungan', 'ID', 4265, '211', 1, 1),
(48809, 'Kabupaten Malinau', 'ID', 4265, '211', 1, 1),
(48810, 'Kabupaten Nunukan', 'ID', 4265, '211', 1, 1),
(48811, 'Kabupaten Tana Tidung', 'ID', 4265, '211', 1, 1),
(48812, 'Kota Tarakan', 'ID', 4265, '261', 1, 1),
(48813, 'Kabupaten Bolaang Mongondow', 'ID', 1695, '211', 1, 1),
(48814, 'Kabupaten Bolaang Mongondow Se', 'ID', 1695, '211', 1, 1),
(48815, 'Kabupaten Bolaang Mongondow Ti', 'ID', 1695, '211', 1, 1),
(48816, 'Kabupaten Bolaang Mongondow Ut', 'ID', 1695, '211', 1, 1),
(48817, 'Kabupaten Kepulauan Sangihe', 'ID', 1695, '211', 1, 1),
(48818, 'Kabupaten Kepulauan Siau Tagul', 'ID', 1695, '211', 1, 1),
(48819, 'Kabupaten Kepulauan Talaud', 'ID', 1695, '211', 1, 1),
(48820, 'Kabupaten Minahasa', 'ID', 1695, '211', 1, 1),
(48821, 'Kabupaten Minahasa Selatan', 'ID', 1695, '211', 1, 1),
(48822, 'Kabupaten Minahasa Tenggara', 'ID', 1695, '211', 1, 1),
(48823, 'Kabupaten Minahasa Utara', 'ID', 1695, '211', 1, 1),
(48824, 'Bitung', 'ID', 1695, '261', 1, 1),
(48825, 'Kotamobagu', 'ID', 1695, '261', 1, 1),
(48826, 'Manado', 'ID', 1695, '261', 1, 1),
(48828, 'Mamuju', 'ID', 4243, '261', 1, 1),
(48829, 'Kabupaten Halmahera Barat', 'ID', 1685, '211', 1, 1),
(48830, 'Kabupaten Halmahera Tengah', 'ID', 1685, '211', 1, 1),
(48831, 'Kabupaten Halmahera Utara', 'ID', 1685, '211', 1, 1),
(48832, 'Kabupaten Halmahera Selatan', 'ID', 1685, '211', 1, 1),
(48833, 'Kabupaten Kepulauan Sula', 'ID', 1685, '211', 1, 1),
(48834, 'Kabupaten Halmahera Timur', 'ID', 1685, '211', 1, 1),
(48835, 'Kabupaten Pulau Morotai', 'ID', 1685, '211', 1, 1),
(48836, 'Kabupaten Pulau Taliabu', 'ID', 1685, '211', 1, 1),
(48837, 'Kota Tangerang Selatan', 'ID', 1669, '', 0, 0),
(48838, 'Tangerang Selatan', 'ID', 1669, '261', 1, 1),
(48839, '\'Abadlah', 'ID', 1677, '', 0, 0),
(48841, 'Kota Tangerang Selatan', 'ID', 4242, '', 0, 0),
(48842, 'Kabupaten Gowa', 'ID', 1697, '', 0, 0),
(48844, 'Binjai', 'ID', 1695, '', 0, 0),
(48845, 'Danau Sarang Elang', 'ID', 1674, '', 0, 0),
(48848, 'Solo', 'ID', 1676, '', 0, 1),
(48849, 'Solo', 'ID', 4242, '', 0, 1),
(48850, 'Solo', 'ID', 1696, '', 0, 1),
(48851, 'Kabupaten Tangerang', 'ID', 4242, '', 0, 1),
(48852, 'Cianten', 'ID', 1675, '', 0, 1),
(48854, 'purwokerto', 'ID', 1676, '', 0, 1),
(48855, 'Pabuaran barat', 'ID', 1675, '', 0, 1),
(48856, 'Kabupaten Tangerang', 'ID', 1679, '', 0, 1),
(48857, 'Ciledug Street', 'ID', 4790, '', 0, 1),
(48858, 'Kabupaten Tangerang', 'ID', 4790, '', 0, 1),
(48859, 'bambuapus', 'ID', 4790, '', 0, 1),
(48860, 'Jombang', 'ID', 4790, '', 0, 1),
(48862, 'Jakarta', 'ID', 4242, '', 0, 1),
(48863, 'Pamulang', 'ID', 4790, '', 0, 1),
(48864, 'Kp.Dukuh', 'ID', 4790, '', 0, 1),
(48867, 'Pondok aren', 'ID', 4790, '', 0, 1),
(48868, 'Kabupaten Agam', 'ID', 4790, '', 0, 1),
(48869, 'Aqchah', 'ID', 1669, '', 0, 1),
(48870, 'Pondok aren', 'ID', 4242, '', 0, 1),
(48872, 'Pamulang', 'ID', 4242, '', 0, 1),
(48873, 'Jakarta', 'ID', 4790, '', 0, 1),
(48874, 'Jakatta', 'ID', 1697, '', 0, 1),
(48875, 'Jakarta', 'ID', 1672, '', 0, 1),
(48876, 'Kabupaten Bangka Tengah', 'ID', 4242, '', 0, 1),
(48877, 'Jakarta', 'ID', 1675, '', 0, 1),
(48878, 'Jakarta', 'ID', 1677, '', 0, 1),
(48880, 'Kabupaten Tangerang', 'ID', 1667, '', 0, 1),
(48883, 'Kota bekasi', 'ID', 1675, '', 0, 1),
(48885, 'Bekasi', 'ID', 4242, '', 0, 1),
(48886, 'Jakarta', 'ID', 1669, '', 0, 1),
(48887, 'Banda Aceh', 'ID', 4794, '', 0, 1),
(48888, 'Paku Jaya', 'ID', 4790, '', 0, 1),
(48890, 'Ciledug', 'ID', 4242, '', 0, 1),
(48891, 'Kabupaten Brebes', 'ID', 4795, '', 0, 1),
(48894, 'Jakarta', 'ID', 4797, '', 0, 1),
(48895, 'Jakarta', 'ID', 4798, '', 0, 1),
(48896, 'Depok', 'ID', 4798, '', 0, 1),
(48897, 'Ciledug Street', 'ID', 4242, '', 0, 1),
(48898, 'Jakarta', 'ID', 4799, '', 0, 1),
(48899, 'Ibu kota jakarta', 'ID', 4800, '', 0, 1),
(48900, 'Cilacap Jawa tengah', 'ID', 4801, '', 0, 1),
(48901, 'Bogor timur', 'ID', 4797, '', 0, 1),
(48902, 'Bogor timur', 'ID', 1675, '', 0, 1),
(48904, 'Depok', 'ID', 4797, '', 0, 1),
(48905, 'Ibu kota jakarta', 'ID', 4798, '', 0, 1),
(48906, 'Ibu kota jakarta', 'ID', 4242, '', 0, 1),
(48907, 'Kabupaten Kerinci', 'ID', 4802, '', 0, 1),
(48908, 'Bekasi', 'ID', 4798, '', 0, 1),
(48911, 'bekasi', 'ID', 4802, '', 0, 1),
(48913, 'Jakarta', 'ID', 4803, '', 0, 1),
(48914, 'Ibu kota jakarta', 'ID', 4803, '', 0, 1),
(48916, 'jakarta', 'ID', 4800, '', 0, 1),
(48917, 'Bekasi', 'ID', 4804, '', 0, 1),
(48918, 'Depok', 'ID', 4804, '', 0, 1),
(48919, 'Jakarta utara', 'ID', 4804, '', 0, 1),
(48920, 'jakarta barat', 'ID', 4804, '', 0, 1),
(48921, 'Ibu kota jakarta', 'ID', 4799, '', 0, 1),
(48922, 'tangerang', 'ID', 4801, '', 0, 1),
(48923, 'Bogor timur', 'ID', 4803, '', 0, 1),
(48924, 'Purbalingga', 'ID', 1676, '', 0, 1),
(48925, 'Bogor timur', 'ID', 4798, '', 0, 1),
(48926, 'Kota bambu', 'ID', 4803, '', 0, 1),
(48927, 'batam', 'ID', 4805, '', 0, 1),
(48932, 'Jakarta', 'ID', 4804, '', 0, 1),
(48934, 'Bekasi', 'ID', 4803, '', 0, 1),
(48935, 'Ibu kota jakarta', 'ID', 1675, '', 0, 1),
(48936, 'Tegal', 'ID', 4798, '', 0, 1),
(48938, 'Kabupaten Bogor', 'ID', 4242, '', 0, 1),
(48941, 'Kabupaten Bekasi', 'ID', 4242, '', 0, 1),
(48942, 'Kabupaten Lampung Timur', 'ID', 1675, '', 0, 1),
(48944, 'Kabupaten Blora', 'ID', 1669, '', 0, 1),
(48945, 'Kabupaten Hulu Sungai Selatan', 'ID', 4242, '', 0, 1),
(48947, 'Cengkareng', 'ID', 4242, '', 0, 1),
(48949, 'Kabupaten Intan Jaya', 'ID', 4242, '', 0, 1),
(48950, 'Kabupaten Aceh Singkil', 'ID', 1670, '', 0, 1),
(48954, 'Kabupaten Banyumas', 'ID', 4242, '', 0, 1),
(48957, 'Mataram', 'ID', 4242, '', 0, 1),
(48958, 'Kabupaten Aceh Besar', 'ID', 1678, '', 0, 1),
(48959, 'Kabupaten Bandung', 'ID', 1669, '', 0, 1),
(48960, 'Kabupaten Bogor', 'ID', 4807, '', 0, 1),
(48962, 'Kabupaten Aceh Jaya', 'ID', 4808, '', 0, 1),
(48963, 'Kabupaten Cilacap', 'ID', 4242, '', 0, 1),
(48964, 'Bogor', 'ID', 4242, '', 0, 1),
(48965, 'Bajram Curri', 'ID', 4809, '', 0, 1),
(48966, 'Kabupaten Aceh Selatan', 'ID', 1668, '', 0, 1),
(48967, 'Kabupaten Aceh Tenggara', 'ID', 1669, '', 0, 1),
(48968, 'Kabupaten Aceh Barat Daya', 'ID', 4242, '', 0, 1),
(48969, 'Jakarta Barat', 'ID', 1675, '', 0, 1),
(48970, 'Kabupaten Tulungagung', 'ID', 1676, '', 0, 1),
(48971, 'Kabupaten Bandung', 'ID', 4242, '', 0, 1),
(48972, 'Jakarta Timur', 'ID', 1675, '', 0, 1),
(48973, 'Kabupaten Badung', 'ID', 1675, '', 0, 1),
(48974, 'Kabupaten Bogor', 'ID', 1676, '', 0, 1),
(48975, 'Jakarta Selatan', 'ID', 1669, '', 0, 1),
(48976, 'Kabupaten Halmahera Timur', 'ID', 1667, '', 0, 1),
(48977, 'Kabupaten Jepara', 'ID', 4242, '', 0, 1),
(48979, 'Kabupaten Cirebon', 'ID', 4242, '', 0, 1),
(48980, 'Kabupaten Kebumen', 'ID', 4242, '', 0, 1),
(48982, 'Yogyakarta', 'ID', 4242, '', 0, 1),
(48983, 'Kabupaten Cianjur', 'ID', 1669, '', 0, 1),
(48986, 'Tangerang', 'ID', 1675, '', 0, 1),
(48987, 'Kabupaten Aceh Barat', 'ID', 1667, '', 0, 1),
(48988, 'Kabupaten Tangerang', 'ID', 1675, '', 0, 1),
(48989, 'Kabupaten Aceh Selatan', 'ID', 1676, '', 0, 1),
(48990, 'Kabupaten Bangka Selatan', 'ID', 4242, '', 0, 1),
(48991, 'Kabupaten Serang', 'ID', 4810, '', 0, 1),
(48992, 'Kabupaten Berau', 'ID', 4242, '', 0, 1),
(48993, 'Jakarta Barat', 'ID', 1669, '', 0, 1),
(48994, 'Kabupaten Barito Kuala', 'ID', 4242, '', 0, 1),
(48995, 'Tangerang Selatan', 'ID', 4242, '', 0, 1),
(48996, 'Kabupaten Bekasi', 'ID', 1676, '', 0, 1),
(48997, 'Kabupaten Pati', 'ID', 4242, '', 0, 1),
(48998, 'Kabupaten Lamongan', 'ID', 4242, '', 0, 1),
(48999, 'Kabupaten Purwakarta', 'ID', 4811, '', 0, 1),
(49001, 'Kabupaten Padang Pariaman', 'ID', 4242, '', 0, 1),
(49002, 'Kabupaten Aceh Selatan', 'ID', 1674, '', 0, 1),
(49003, 'Kabupaten Bolaang Mongondow', 'ID', 4242, '', 0, 1),
(49004, 'Bandung', 'ID', 1669, '', 0, 1),
(49005, 'Jakarta Selatan', 'ID', 1675, '', 0, 1),
(49006, 'Kabupaten Administrasi Kepulau', 'ID', 1679, '', 0, 1),
(49007, 'Kabupaten Banggai Kepulauan', 'ID', 1674, '', 0, 1),
(49008, 'Banjarmasin', 'ID', 1680, '', 0, 1),
(49009, 'Kabupaten Barito Timur', 'ID', 4242, '', 0, 1),
(49010, '- Select a City -', 'ID', 4812, '', 0, 1),
(49012, 'Kabupaten Indramayu', 'ID', 4242, '', 0, 1),
(49013, 'Kabupaten Asmat', 'ID', 4813, '', 0, 1),
(49014, 'Kabupaten Banyuasin', 'ID', 1680, '', 0, 1),
(49015, 'Yogyakarta', 'ID', 1675, '', 0, 1),
(49016, 'Kabupaten Kepulauan Aru', 'ID', 4242, '', 0, 1),
(49017, 'Kabupaten Serang', 'ID', 1675, '', 0, 1),
(49018, 'Kabupaten Bandung', 'ID', 1677, '', 0, 1),
(49021, 'Gorontalo', 'ID', 4242, '', 0, 1),
(49022, 'Tangerang', 'ID', 4242, '', 0, 1),
(49023, 'Kabupaten Badung', 'ID', 4242, '', 0, 1),
(49024, 'Kabupaten Bandung', 'ID', 1676, '', 0, 1),
(49026, 'Kabupaten Malang', 'ID', 1670, '', 0, 1),
(49028, 'Kabupaten Kotabaru', 'ID', 1669, '', 0, 1),
(49029, 'Kabupaten Seluma', 'ID', 4242, '', 0, 1),
(49031, 'Kabupaten Boyolali', 'ID', 4242, '', 0, 1),
(49032, 'Kabupaten Purwakarta', 'ID', 1699, '', 0, 1),
(49033, 'Kabupaten Bandung Barat', 'ID', 4242, '', 0, 1),
(49034, 'Kabupaten Karawang', 'ID', 4242, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `country_id` varchar(2) NOT NULL,
  `iso_code_2` varchar(2) DEFAULT NULL,
  `iso_code_3` varchar(3) DEFAULT NULL,
  `name` varchar(70) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `default` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`country_id`, `iso_code_2`, `iso_code_3`, `name`, `phone_code`, `order`, `default`, `active`) VALUES
('AD', 'AD', 'AND', 'Andorra', '+376', 6, 0, 1),
('AE', 'AE', 'ARE', 'United Arab Emirates', '+971', 233, 0, 1),
('AF', 'AF', 'AFG', 'Afghanistan', '+93', 2, 0, 1),
('AG', 'AG', 'ATG', 'Antigua and Barbuda', '+1', 10, 0, 1),
('AI', 'AI', 'AIA', 'Anguilla', '+809', 8, 0, 1),
('AL', 'AL', 'ALB', 'Albania', '+355', 3, 0, 1),
('AM', 'AM', 'ARM', 'Armenia', '+374', 12, 0, 1),
('AN', 'AN', 'ANT', 'Netherlands Antilles', '+599', 157, 0, 1),
('AO', 'AO', 'AGO', 'Angola', '+244', 7, 0, 1),
('AQ', 'AQ', 'ATA', 'Antarctica', '+672', 9, 0, 1),
('AR', 'AR', 'ARG', 'Argentina', '+54', 11, 0, 1),
('AS', 'AS', 'ASM', 'American Samoa', '+684', 5, 0, 1),
('AT', 'AT', 'AUT', 'Austria', '+43', 15, 0, 1),
('AU', 'AU', 'AUS', 'Australia', '+61', 14, 0, 1),
('AW', 'AW', 'ABW', 'Aruba', '+297', 13, 0, 1),
('AX', 'AX', 'ALA', 'Aaland Islands', '+358', 1, 0, 1),
('AZ', 'AZ', 'AZE', 'Azerbaijan', '+994', 16, 0, 1),
('BA', 'BA', 'BIH', 'Bosnia and Herzegovina', '+387', 29, 0, 1),
('BB', 'BB', 'BRB', 'Barbados', '+246', 20, 0, 1),
('BD', 'BD', 'BGD', 'Bangladesh', '+880', 19, 0, 1),
('BE', 'BE', 'BEL', 'Belgium', '+32', 22, 0, 1),
('BF', 'BF', 'BFA', 'Burkina Faso', '+226', 36, 0, 1),
('BG', 'BG', 'BGR', 'Bulgaria', '+359', 35, 0, 1),
('BH', 'BH', 'BHR', 'Bahrain', '+973', 18, 0, 1),
('BI', 'BI', 'BDI', 'Burundi', '+257', 37, 0, 1),
('BJ', 'BJ', 'BEN', 'Benin', '+229', 24, 0, 1),
('BL', 'BL', 'BLM', 'St. Barthelemy', '+590', 207, 0, 1),
('BM', 'BM', 'BMU', 'Bermuda', '+809', 25, 0, 1),
('BN', 'BN', 'BRN', 'Brunei Darussalam', '+673', 34, 0, 1),
('BO', 'BO', 'BOL', 'Bolivia', '+591', 27, 0, 1),
('BQ', 'BQ', 'BES', 'Bonaire, Sint Eustatius and Saba', '+599', 28, 0, 1),
('BR', 'BR', 'BRA', 'Brazil', '+55', 32, 0, 1),
('BS', 'BS', 'BHS', 'Bahamas', '+242', 17, 0, 1),
('BT', 'BT', 'BTN', 'Bhutan', '+975', 26, 0, 1),
('BV', 'BV', 'BVT', 'Bouvet Island', '+47', 31, 0, 1),
('BW', 'BW', 'BWA', 'Botswana', '+267', 30, 0, 1),
('BY', 'BY', 'BLR', 'Belarus', '+375', 21, 0, 1),
('BZ', 'BZ', 'BLZ', 'Belize', '+501', 23, 0, 1),
('CA', 'CA', 'CAN', 'Canada', '+1', 40, 0, 1),
('CC', 'CC', 'CCK', 'Cocos (Keeling) Islands', '+891', 49, 0, 1),
('CD', 'CD', 'COD', 'Democratic Republic of Congo', '+243', 61, 0, 1),
('CF', 'CF', 'CAF', 'Central African Republic', '+236', 44, 0, 1),
('CG', 'CG', 'COG', 'Congo', '+242', 52, 0, 1),
('CH', 'CH', 'CHE', 'Switzerland', '+41', 216, 0, 1),
('CI', 'CI', 'CIV', 'Cote D\'Ivoire', '+225', 55, 0, 1),
('CK', 'CK', 'COK', 'Cook Islands', '+682', 53, 0, 1),
('CL', 'CL', 'CHL', 'Chile', '+56', 46, 0, 1),
('CM', 'CM', 'CMR', 'Cameroon', '+237', 39, 0, 1),
('CN', 'CN', 'CHN', 'China', '+86', 47, 0, 1),
('CO', 'CO', 'COL', 'Colombia', '+57', 50, 0, 1),
('CR', 'CR', 'CRI', 'Costa Rica', '+506', 54, 0, 1),
('CU', 'CU', 'CUB', 'Cuba', '+53', 57, 0, 1),
('CV', 'CV', 'CPV', 'Cape Verde', '+238', 42, 0, 1),
('CW', 'CW', 'CUW', 'Curacao', '+599', 58, 0, 1),
('CX', 'CX', 'CXR', 'Christmas Island', '+61', 48, 0, 1),
('CY', 'CY', 'CYP', 'Cyprus', '+357', 59, 0, 1),
('CZ', 'CZ', 'CZE', 'Czech Republic', '+420', 60, 0, 1),
('DE', 'DE', 'DEU', 'Germany', '+49', 86, 0, 1),
('DJ', 'DJ', 'DJI', 'Djibouti', '+253', 63, 0, 1),
('DK', 'DK', 'DNK', 'Denmark', '+45', 62, 0, 1),
('DM', 'DM', 'DMA', 'Dominica', '+1', 64, 0, 1),
('DO', 'DO', 'DOM', 'Dominican Republic', '+809', 65, 0, 1),
('DZ', 'DZ', 'DZA', 'Algeria', '+213', 4, 0, 1),
('EC', 'EC', 'ECU', 'Ecuador', '+593', 67, 0, 1),
('EE', 'EE', 'EST', 'Estonia', '+372', 72, 0, 1),
('EG', 'EG', 'EGY', 'Egypt', '+20', 68, 0, 1),
('EH', 'EH', 'ESH', 'Western Sahara', '+212', 246, 0, 1),
('ER', 'ER', 'ERI', 'Eritrea', '+291', 71, 0, 1),
('ES', 'ES', 'ESP', 'Spain', '+34', 205, 0, 1),
('ET', 'ET', 'ETH', 'Ethiopia', '+251', 73, 0, 1),
('FI', 'FI', 'FIN', 'Finland', '+358', 77, 0, 1),
('FJ', 'FJ', 'FJI', 'Fiji', '+679', 76, 0, 1),
('FK', 'FK', 'FLK', 'Falkland Islands (Malvinas)', '+500', 74, 0, 1),
('FM', 'FM', 'FSM', 'Micronesia, Federated States of', '+691', 144, 0, 1),
('FO', 'FO', 'FRO', 'Faroe Islands', '+298', 75, 0, 1),
('FR', 'FR', 'FRA', 'France, Metropolitan', '+33', 78, 0, 1),
('GA', 'GA', 'GAB', 'Gabon', '+241', 83, 0, 1),
('GB', 'GB', 'GBR', 'United Kingdom', '+44', 234, 0, 1),
('GD', 'GD', 'GRD', 'Grenada', '+1', 91, 0, 1),
('GE', 'GE', 'GEO', 'Georgia', '+995', 85, 0, 1),
('GF', 'GF', 'GUF', 'French Guiana', '+594', 79, 0, 1),
('GG', 'GG', 'GGY', 'Guernsey', '+44', 95, 0, 1),
('GH', 'GH', 'GHA', 'Ghana', '+233', 87, 0, 1),
('GI', 'GI', 'GIB', 'Gibraltar', '+350', 88, 0, 1),
('GL', 'GL', 'GRL', 'Greenland', '+299', 90, 0, 1),
('GM', 'GM', 'GMB', 'Gambia', '+220', 84, 0, 1),
('GN', 'GN', 'GIN', 'Guinea', '+224', 96, 0, 1),
('GP', 'GP', 'GLP', 'Guadeloupe', '+590', 92, 0, 1),
('GQ', 'GQ', 'GNQ', 'Equatorial Guinea', '+240', 70, 0, 1),
('GR', 'GR', 'GRC', 'Greece', '+30', 89, 0, 1),
('GS', 'GS', 'SGS', 'South Georgia &amp; South Sandwich Islands', '+995', 203, 0, 1),
('GT', 'GT', 'GTM', 'Guatemala', '+502', 94, 0, 1),
('GU', 'GU', 'GUM', 'Guam', '+671', 93, 0, 1),
('GW', 'GW', 'GNB', 'Guinea-Bissau', '+245', 97, 0, 1),
('GY', 'GY', 'GUY', 'Guyana', '+592', 98, 0, 1),
('HK', 'HK', 'HKG', 'Hong Kong', '+852', 102, 0, 1),
('HM', 'HM', 'HMD', 'Heard and Mc Donald Islands', '+672', 100, 0, 1),
('HN', 'HN', 'HND', 'Honduras', '+504', 101, 0, 1),
('HR', 'HR', 'HRV', 'Croatia', '+385', 56, 0, 1),
('HT', 'HT', 'HTI', 'Haiti', '+509', 99, 0, 1),
('HU', 'HU', 'HUN', 'Hungary', '+36', 103, 0, 1),
('IC', 'IC', 'ICA', 'Canary Islands', '+91', 41, 0, 1),
('ID', 'ID', 'IDN', 'Indonesia', '+62', 106, 0, 1),
('IE', 'IE', 'IRL', 'Ireland', '+353', 109, 0, 1),
('IL', 'IL', 'ISR', 'Israel', '+972', 110, 0, 1),
('IN', 'IN', 'IND', 'India', '+91', 105, 0, 1),
('IO', 'IO', 'IOT', 'British Indian Ocean Territory', '+246', 33, 0, 1),
('IQ', 'IQ', 'IRQ', 'Iraq', '+964', 108, 0, 1),
('IR', 'IR', 'IRN', 'Iran (Islamic Republic of)', '+98', 107, 0, 1),
('IS', 'IS', 'ISL', 'Iceland', '+354', 104, 0, 1),
('IT', 'IT', 'ITA', 'Italy', '+39', 111, 0, 1),
('JE', 'JE', 'JEY', 'Jersey', '+44', 114, 0, 1),
('JM', 'JM', 'JAM', 'Jamaica', '+876', 112, 0, 1),
('JO', 'JO', 'JOR', 'Jordan', '+962', 115, 0, 1),
('JP', 'JP', 'JPN', 'Japan', '+81', 113, 0, 1),
('KE', 'KE', 'KEN', 'Kenya', '+254', 117, 0, 1),
('KG', 'KG', 'KGZ', 'Kyrgyzstan', '+996', 121, 0, 1),
('KH', 'KH', 'KHM', 'Cambodia', '+855', 38, 0, 1),
('KI', 'KI', 'KIR', 'Kiribati', '+686', 118, 0, 1),
('KM', 'KM', 'COM', 'Comoros', '+269', 51, 0, 1),
('KN', 'KN', 'KNA', 'Saint Kitts and Nevis', '+1', 186, 0, 1),
('KP', 'KP', 'PRK', 'North Korea', '+850', 165, 0, 1),
('KR', 'KR', 'KOR', 'Korea, Republic of', '+82', 119, 0, 1),
('KW', 'KW', 'KWT', 'Kuwait', '+965', 120, 0, 1),
('KY', 'KY', 'CYM', 'Cayman Islands', '+345', 43, 0, 1),
('KZ', 'KZ', 'KAZ', 'Kazakhstan', '+7', 116, 0, 1),
('LA', 'LA', 'LAO', 'Lao People\'s Democratic Republic', '+856', 122, 0, 1),
('LB', 'LB', 'LBN', 'Lebanon', '+961', 124, 0, 1),
('LC', 'LC', 'LCA', 'Saint Lucia', '+1', 187, 0, 1),
('LI', 'LI', 'LIE', 'Liechtenstein', '+423', 128, 0, 1),
('LK', 'LK', 'LKA', 'Sri Lanka', '+94', 206, 0, 1),
('LR', 'LR', 'LBR', 'Liberia', '+231', 126, 0, 1),
('LS', 'LS', 'LSO', 'Lesotho', '+266', 125, 0, 1),
('LT', 'LT', 'LTU', 'Lithuania', '+370', 129, 0, 1),
('LU', 'LU', 'LUX', 'Luxembourg', '+352', 130, 0, 1),
('LV', 'LV', 'LVA', 'Latvia', '+371', 123, 0, 1),
('LY', 'LY', 'LBY', 'Libyan Arab Jamahiriya', '+218', 127, 0, 1),
('MA', 'MA', 'MAR', 'Morocco', '+212', 150, 0, 1),
('MC', 'MC', 'MCO', 'Monaco', '+33', 146, 0, 1),
('MD', 'MD', 'MDA', 'Moldova, Republic of', '+373', 145, 0, 1),
('ME', 'ME', 'MNE', 'Montenegro', '+382', 148, 0, 1),
('MF', 'MF', 'MAF', 'St. Martin (French part)', '+1', 209, 0, 1),
('MG', 'MG', 'MDG', 'Madagascar', '+261', 132, 0, 1),
('MH', 'MH', 'MHL', 'Marshall Islands', '+692', 138, 0, 1),
('MK', 'MK', 'MKD', 'Macedonia (FYROM)', '+389', 82, 0, 1),
('ML', 'ML', 'MLI', 'Mali', '+223', 136, 0, 1),
('MM', 'MM', 'MMR', 'Myanmar', '+95', 152, 0, 1),
('MN', 'MN', 'MNG', 'Mongolia', '+976', 147, 0, 1),
('MO', 'MO', 'MAC', 'Macau', '+853', 131, 0, 1),
('MP', 'MP', 'MNP', 'Northern Mariana Islands', '+1', 166, 0, 1),
('MQ', 'MQ', 'MTQ', 'Martinique', '+596', 139, 0, 1),
('MR', 'MR', 'MRT', 'Mauritania', '+222', 140, 0, 1),
('MS', 'MS', 'MSR', 'Montserrat', '+473', 149, 0, 1),
('MT', 'MT', 'MLT', 'Malta', '+356', 137, 0, 1),
('MU', 'MU', 'MUS', 'Mauritius', '+230', 141, 0, 1),
('MV', 'MV', 'MDV', 'Maldives', '+960', 135, 0, 1),
('MW', 'MW', 'MWI', 'Malawi', '+265', 133, 0, 1),
('MX', 'MX', 'MEX', 'Mexico', '+52', 143, 0, 1),
('MY', 'MY', 'MYS', 'Malaysia', '+60', 134, 0, 1),
('MZ', 'MZ', 'MOZ', 'Mozambique', '+258', 151, 0, 1),
('NA', 'NA', 'NAM', 'Namibia', '+264', 153, 0, 1),
('NC', 'NC', 'NCL', 'New Caledonia', '+687', 158, 0, 1),
('NE', 'NE', 'NER', 'Niger', '+227', 161, 0, 1),
('NF', 'NF', 'NFK', 'Norfolk Island', '+672', 164, 0, 1),
('NG', 'NG', 'NGA', 'Nigeria', '+234', 162, 0, 1),
('NI', 'NI', 'NIC', 'Nicaragua', '+505', 160, 0, 1),
('NL', 'NL', 'NLD', 'Netherlands', '+31', 156, 0, 1),
('NO', 'NO', 'NOR', 'Norway', '+47', 167, 0, 1),
('NP', 'NP', 'NPL', 'Nepal', '+977', 155, 0, 1),
('NR', 'NR', 'NRU', 'Nauru', '+674', 154, 0, 1),
('NU', 'NU', 'NIU', 'Niue', '+683', 163, 0, 1),
('NZ', 'NZ', 'NZL', 'New Zealand', '+64', 159, 0, 1),
('OM', 'OM', 'OMN', 'Oman', '+968', 168, 0, 1),
('PA', 'PA', 'PAN', 'Panama', '+507', 172, 0, 1),
('PE', 'PE', 'PER', 'Peru', '+51', 175, 0, 1),
('PF', 'PF', 'PYF', 'French Polynesia', '+689', 80, 0, 1),
('PG', 'PG', 'PNG', 'Papua New Guinea', '+675', 173, 0, 1),
('PH', 'PH', 'PHL', 'Philippines', '+63', 176, 0, 1),
('PK', 'PK', 'PAK', 'Pakistan', '+92', 169, 0, 1),
('PL', 'PL', 'POL', 'Poland', '+48', 178, 0, 1),
('PM', 'PM', 'SPM', 'St. Pierre and Miquelon', '+508', 210, 0, 1),
('PN', 'PN', 'PCN', 'Pitcairn', '+64', 177, 0, 1),
('PR', 'PR', 'PRI', 'Puerto Rico', '+1', 180, 0, 1),
('PS', 'PS', 'PSE', 'Palestinian Territory, Occupied', '+970', 171, 0, 1),
('PT', 'PT', 'PRT', 'Portugal', '+351', 179, 0, 1),
('PW', 'PW', 'PLW', 'Palau', '+680', 170, 0, 1),
('PY', 'PY', 'PRY', 'Paraguay', '+595', 174, 0, 1),
('QA', 'QA', 'QAT', 'Qatar', '+974', 181, 0, 1),
('RE', 'RE', 'REU', 'Reunion', '+262', 182, 0, 1),
('RO', 'RO', 'ROM', 'Romania', '+40', 183, 0, 1),
('RS', 'RS', 'SRB', 'Serbia', '+381', 194, 0, 1),
('RU', 'RU', 'RUS', 'Russian Federation', '+7', 184, 0, 1),
('RW', 'RW', 'RWA', 'Rwanda', '+250', 185, 0, 1),
('SA', 'SA', 'SAU', 'Saudi Arabia', '+966', 192, 0, 1),
('SB', 'SB', 'SLB', 'Solomon Islands', '+677', 200, 0, 1),
('SC', 'SC', 'SYC', 'Seychelles', '+248', 195, 0, 1),
('SD', 'SD', 'SDN', 'Sudan', '+249', 211, 0, 1),
('SE', 'SE', 'SWE', 'Sweden', '+46', 215, 0, 1),
('SG', 'SG', 'SGP', 'Singapore', '+65', 197, 0, 1),
('SH', 'SH', 'SHN', 'St. Helena', '+290', 208, 0, 1),
('SI', 'SI', 'SVN', 'Slovenia', '+386', 199, 0, 1),
('SJ', 'SJ', 'SJM', 'Svalbard and Jan Mayen Islands', '+47', 213, 0, 1),
('SK', 'SK', 'SVK', 'Slovak Republic', '+421', 198, 0, 1),
('SL', 'SL', 'SLE', 'Sierra Leone', '+232', 196, 0, 1),
('SM', 'SM', 'SMR', 'San Marino', '+378', 190, 0, 1),
('SN', 'SN', 'SEN', 'Senegal', '+221', 193, 0, 1),
('SO', 'SO', 'SOM', 'Somalia', '+252', 201, 0, 1),
('SR', 'SR', 'SUR', 'Suriname', '+597', 212, 0, 1),
('SS', 'SS', 'SSD', 'South Sudan', '+211', 204, 0, 1),
('ST', 'ST', 'STP', 'Sao Tome and Principe', '+239', 191, 0, 1),
('SV', 'SV', 'SLV', 'El Salvador', '+503', 69, 0, 1),
('SY', 'SY', 'SYR', 'Syrian Arab Republic', '+963', 217, 0, 1),
('SZ', 'SZ', 'SWZ', 'Swaziland', '+268', 214, 0, 1),
('TC', 'TC', 'TCA', 'Turks and Caicos Islands', '+1', 229, 0, 1),
('TD', 'TD', 'TCD', 'Chad', '+235', 45, 0, 1),
('TF', 'TF', 'ATF', 'French Southern Territories', '+596', 81, 0, 1),
('TG', 'TG', 'TGO', 'Togo', '+228', 222, 0, 1),
('TH', 'TH', 'THA', 'Thailand', '+66', 221, 0, 1),
('TJ', 'TJ', 'TJK', 'Tajikistan', '+7', 219, 0, 1),
('TK', 'TK', 'TKL', 'Tokelau', '+690', 223, 0, 1),
('TL', 'TL', 'TLS', 'East Timor', '+670', 66, 0, 1),
('TM', 'TM', 'TKM', 'Turkmenistan', '+993', 228, 0, 1),
('TN', 'TN', 'TUN', 'Tunisia', '+216', 226, 0, 1),
('TO', 'TO', 'TON', 'Tonga', '+676', 224, 0, 1),
('TR', 'TR', 'TUR', 'Turkey', '+90', 227, 0, 1),
('TT', 'TT', 'TTO', 'Trinidad and Tobago', '+1', 225, 0, 1),
('TV', 'TV', 'TUV', 'Tuvalu', '+688', 230, 0, 1),
('TW', 'TW', 'TWN', 'Taiwan', '+886', 218, 0, 1),
('TZ', 'TZ', 'TZA', 'Tanzania, United Republic of', '+255', 220, 0, 1),
('UA', 'UA', 'UKR', 'Ukraine', '+380', 232, 0, 1),
('UG', 'UG', 'UGA', 'Uganda', '+256', 231, 0, 1),
('UM', 'UM', 'UMI', 'United States Minor Outlying Islands', '+1', 236, 0, 1),
('US', 'US', 'USA', 'United States', '+1', 235, 0, 1),
('UY', 'UY', 'URY', 'Uruguay', '+598', 237, 0, 1),
('UZ', 'UZ', 'UZB', 'Uzbekistan', '+7', 238, 0, 1),
('VA', 'VA', 'VAT', 'Vatican City State (Holy See)', '+379', 240, 0, 1),
('VC', 'VC', 'VCT', 'Saint Vincent and the Grenadines', '+1', 188, 0, 1),
('VE', 'VE', 'VEN', 'Venezuela', '+58', 241, 0, 1),
('VG', 'VG', 'VGB', 'Virgin Islands (British)', '+1', 243, 0, 1),
('VI', 'VI', 'VIR', 'Virgin Islands (U.S.)', '+1', 244, 0, 1),
('VN', 'VN', 'VNM', 'Viet Nam', '+84', 242, 0, 1),
('VU', 'VU', 'VUT', 'Vanuatu', '+678', 239, 0, 1),
('WF', 'WF', 'WLF', 'Wallis and Futuna Islands', '+681', 245, 0, 1),
('WS', 'WS', 'WSM', 'Samoa', '+685', 189, 0, 1),
('YE', 'YE', 'YEM', 'Yemen', '+967', 247, 0, 1),
('YT', 'YT', 'MYT', 'Mayotte', '+33', 142, 0, 1),
('ZA', 'ZA', 'ZAF', 'South Africa', '+27', 202, 0, 1),
('ZM', 'ZM', 'ZMB', 'Zambia', '+260', 248, 0, 1),
('ZW', 'ZW', 'ZWE', 'Zimbabwe', '+263', 249, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_admin`
--

CREATE TABLE `edu_admin` (
  `admin_id` int(11) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('M','W') NOT NULL DEFAULT 'M',
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `admin_level` int(11) DEFAULT 0,
  `token_admin` varchar(32) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `country_id` bigint(20) DEFAULT NULL,
  `state_id` bigint(20) DEFAULT NULL,
  `city_id` bigint(20) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `password_initial` varchar(45) DEFAULT NULL,
  `auth` varchar(45) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `time_last_activity` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `ip_last_activity` varchar(40) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_admin`
--

INSERT INTO `edu_admin` (`admin_id`, `school_id`, `name`, `gender`, `birth_place`, `birth_day`, `username`, `admin_level`, `token_admin`, `email`, `phone`, `address`, `country_id`, `state_id`, `city_id`, `password`, `password_initial`, `auth`, `time_create`, `time_edit`, `time_last_activity`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `ip_last_activity`, `blocked`, `active`) VALUES
(1, 1, 'admin', 'M', 'Jambi', '1983-12-10', 'admin', 0, '16be0cb239e2c87b220103926a109077', 'admin@planetbiru.com', '081266612126', '', 0, 0, 0, 'c3284d0f94606de1fd2af172aba15bf3', 'admin', '', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '2017-10-14 00:00:00', 0, 1, '127.0.0.1', '127.0.0.1', '127.0.0.1', 0, 1);

--
-- Triggers `edu_admin`
--
DELIMITER $$
CREATE TRIGGER `after_delete_edu_admin` AFTER DELETE ON `edu_admin` FOR EACH ROW begin
DELETE FROM `edu_member_school` where `role` = 'A' and `member_id` = OLD.`admin_id`;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `edu_answer`
--

CREATE TABLE `edu_answer` (
  `answer_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `test_id` bigint(20) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `answer` text DEFAULT NULL,
  `true` int(11) DEFAULT NULL,
  `false` int(11) DEFAULT NULL,
  `initial_score` double DEFAULT NULL,
  `penalty` double DEFAULT NULL,
  `final_score` double DEFAULT NULL,
  `percent` double DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `edu_article`
--

CREATE TABLE `edu_article` (
  `article_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `open` tinyint(1) DEFAULT 0,
  `class` text DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `member_create` bigint(20) DEFAULT NULL,
  `role_create` char(1) DEFAULT NULL,
  `member_edit` bigint(20) DEFAULT NULL,
  `role_edit` char(1) DEFAULT NULL,
  `ip_create` varchar(40) DEFAULT NULL,
  `ip_edit` varchar(40) DEFAULT NULL,
  `reader` longtext DEFAULT NULL,
  `read` bigint(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `edu_class`
--

CREATE TABLE `edu_class` (
  `class_id` bigint(20) NOT NULL,
  `token_class` varchar(32) DEFAULT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `class_code` varchar(20) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `school_program_id` bigint(20) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `default` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_class`
--

INSERT INTO `edu_class` (`class_id`, `token_class`, `school_id`, `class_code`, `grade_id`, `school_program_id`, `name`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `order`, `default`, `active`) VALUES
(1, 'a69a4af30c8fa7485a968839ce55f993', 1, 'X.IPA1', 10, 1, 'X.IPA1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1012, 0, 1),
(2, '22e02080bb40392714797532aa36e79c', 1, 'X.IPA2', 10, 1, 'X.IPA2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1013, 0, 1),
(3, 'cf9d10ed833c1835ca040c14e6c1def3', 1, 'X.IPA3', 10, 1, 'X.IPA3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1014, 0, 1),
(4, 'f5463f980774cd024763ba5df1bddd0b', 1, 'X.IPA4', 10, 1, 'X.IPA4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1015, 0, 1),
(5, 'c9a06c47872250a7f28e24024a7c2a6d', 1, 'X.IPA5', 10, 1, 'X.IPA5', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1016, 0, 1),
(6, '96c88f81ea2dff535e9a915192f604f2', 1, 'X.IPA6', 10, 1, 'X.IPA6', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1017, 0, 1),
(7, 'ff85e40e1a83c87b7a2b76be50575473', 1, 'X.IPA7', 10, 1, 'X.IPA7', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1018, 0, 1),
(8, 'b43ecd255996391a83f7e6524bbb6ef0', 1, 'X.IPA8', 10, 1, 'X.IPA8', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1019, 0, 1),
(9, '725b043b0f12adbf8900645072a831b9', 1, 'X.IPS1', 10, 2, 'X.IPS1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1030, 0, 1),
(10, '58f1569007407040fb912c27becc5b41', 1, 'X.IPS2', 10, 2, 'X.IPS2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1031, 0, 1),
(11, '9d3ae8eb6e2ab4a50a2ee7388329340c', 1, 'X.IPS3', 10, 2, 'X.IPS3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1032, 0, 1),
(12, '0c6dfc20cc922c91f795ee79a0ac7de0', 1, 'X.IPS4', 10, 2, 'X.IPS4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1033, 0, 1),
(13, '3954cb69458fa89b94adeecea73e7a7c', 1, 'XI.IPA1', 11, 1, 'XI.IPA1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1124, 0, 1),
(14, 'ddf88fd72da32dde79efb18ba0ebfc04', 1, 'XI.IPA2', 11, 1, 'XI.IPA2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1125, 0, 1),
(15, '56994424f54fafc8a02485e5a32c01f5', 1, 'XI.IPA3', 11, 1, 'XI.IPA3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1126, 0, 1),
(16, '06612ac2f162923b9b5183a6455d97d6', 1, 'XI.IPA4', 11, 1, 'XI.IPA4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1127, 0, 1),
(17, 'fba3b297aff46eb93df9a5056c1856d9', 1, 'XI.IPA5', 11, 1, 'XI.IPA5', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1128, 0, 1),
(18, '245f43002ac99014ec178d6755327d62', 1, 'XI.IPA6', 11, 1, 'XI.IPA6', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1129, 0, 1),
(19, 'ceb529e5ab63757298cfddfe390e430e', 1, 'XI.IPA7', 11, 1, 'XI.IPA7', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1130, 0, 1),
(20, '64fc406ad8c877f6d7bd967004bae289', 1, 'XI.IPA8', 11, 1, 'XI.IPA8', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1131, 0, 1),
(21, '946c50d9b53414e6ffc034a8a6d77849', 1, 'XI.IPS1', 11, 2, 'XI.IPS1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1142, 0, 1),
(22, '7d1613e0503208ce52f8c639992ffc82', 1, 'XI.IPS2', 11, 2, 'XI.IPS2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1143, 0, 1),
(23, '875cad0b486f47e7ffeeae677696f389', 1, 'XI.IPS3', 11, 2, 'XI.IPS3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1144, 0, 1),
(24, '33ac66e12ea6302a5aff9d1f1688a526', 1, 'XI.IPS4', 11, 2, 'XI.IPS4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1145, 0, 1),
(25, '67c94b618f8bfeda804d67d6fc5fe07d', 1, 'XII.IPA1', 12, 1, 'XII.IPA1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1236, 0, 1),
(26, '9dbfc00c449bfde9d7dabfbff9acf351', 1, 'XII.IPA2', 12, 1, 'XII.IPA2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1237, 0, 1),
(27, '36073752c76f20f8cb4ddbed445bccce', 1, 'XII.IPA3', 12, 1, 'XII.IPA3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1238, 0, 1),
(28, '3f1c2382947a3034c4756f9cdb7d0aab', 1, 'XII.IPA4', 12, 1, 'XII.IPA4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1239, 0, 1),
(29, '4185bbb4c8347612717c22ce19c59d72', 1, 'XII.IPA5', 12, 1, 'XII.IPA5', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1240, 0, 1),
(30, '8a693de9eee01846a44097914d6b0b7d', 1, 'XII.IPA6', 12, 1, 'XII.IPA6', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1241, 0, 1),
(31, '179772a43586267c79ffa93e7c353fd9', 1, 'XII.IPA7', 12, 1, 'XII.IPA7', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1242, 0, 1),
(32, '9691616fbae87caacb1c2a90ac40fa3d', 1, 'XII.IPA8', 12, 1, 'XII.IPA8', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1243, 0, 1),
(33, '642d2807590b6aa366715713435a3abf', 1, 'XII.IPS1', 12, 2, 'XII.IPS1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1254, 0, 1),
(34, '8662943c3b832cc7a1053a4b7983762b', 1, 'XII.IPS2', 12, 2, 'XII.IPS2', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1255, 0, 1),
(35, 'cb0d99fb4d6c057fca806b62bef2d8c2', 1, 'XII.IPS3', 12, 2, 'XII.IPS3', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1256, 0, 1),
(36, 'ad973aee49017c2f5ee1a378ace9fce8', 1, 'XII.IPS4', 12, 2, 'XII.IPS4', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1257, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_info`
--

CREATE TABLE `edu_info` (
  `info_id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_info`
--

INSERT INTO `edu_info` (`info_id`, `name`, `content`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
(1, 'Format Media', '<div class=\"article-content\">\n\n<p>Planet Edu menetapkan standard media yang dapat dimasukkan ke dalam artikel maupun soal ujian. Standard media ini disesuaikan dengan kompatibilitas browser modern.</p>\n\n<p>Format media yang didukung oleh Planet Edu adalah sebagai berikut:</p>\n\n<ol><li>audio (mp3, m4q, ogg)</li>\n\n<li>video (mp4, mpeg, webm, ogg)</li>\n\n<li>iframe (pdf atau URL web lain)</li>\n\n</ol><p>Khusus untuk video yang disediakan dengan platform web seperti YouTube, Vidio, Dailymotion, dan lain-lain, gunakan IFRAME dengan URL halaman embed yang berisi player untuk video tersebut.</p>\n\n<p>Untuk memasukkan media ke dalam artikel dan ujian melalui editor HTML, pilih icon \"Insert/Edit Embedded Media\" kemudian pilih jenis media sebagaimana gambar berikut:</p>\n\n<p>Setelah jenis media dipilih, masukkan URL media.</p>\n\n<p>Untuk memasukkan media melalui editor soal teks, gunakan awalan seperti contoh sebagai berikut:</p>\n\n<table style=\"width: 100%;\" border=\"1\"><thead><tr><td width=\"20\">No</td>\n\n<td>Media</td>\n\n<td>Awalan</td>\n\n<td>Contoh</td>\n\n</tr></thead><tbody><tr><td style=\"text-align: right;\">1</td>\n\n<td>Gambar</td>\n\n<td>img</td>\n\n<td>img:http://example.com/image/photo1.jpg</td>\n\n</tr><tr><td style=\"text-align: right;\">2</td>\n\n<td>Audio</td>\n\n<td>audio</td>\n\n<td>audio:http://example.com/audio/sound1.mp3</td>\n\n</tr><tr><td style=\"text-align: right;\">3</td>\n\n<td>Video</td>\n\n<td>video</td>\n\n<td>video:http://example.com/video/movie1.mp4</td>\n\n</tr><tr><td style=\"text-align: right;\">4</td>\n\n<td>YouTube, Vidio, Dailymotion</td>\n\n<td>youtube</td>\n\n<td>youtube:https://youtu.be/S56sgdnCXIE</td>\n\n</tr><tr><td style=\"text-align: right;\">5</td>\n\n<td>Dokumen PDF</td>\n\n<td>iframe</td>\n\n<td>iframe:http://sekolah.kamshory.com/proposal.pdf</td>\n\n</tr></tbody></table><p>Media <strong>flash</strong>, <strong>quicktime</strong>, <strong>shockwave</strong>, <strong>windowsmedia</strong> dan <strong>realmedia</strong>, tidak lagi didukung oleh Planet Edu karena banyak browser tidak mendukung format ini. Penggunaan media ini dipastikan akan mengalami masalah di kemudian hari. Oleh karena itu, hindarilah penggunaan media ini untuk keperluan apapun.</p>\n\n<p>Sangat disarankan kepada semua pengguna untuk menggunakan versi terbaru dari salah satu browser di bawah ini:</p>\n\n<ol><li>Mozilla Firefox</li>\n\n<li>Google Chrome</li>\n\n<li>Microsoft Edge atau Microsoft Internet Explorer 11</li>\n\n</ol><p>Khusus untuk pengguna MacBook, apabila penggunaan browser standard mengalami masalah, silakan gunakan browser Mozilla Firefox atau Google Chrome.</p>\n\n</div>', '2017-09-10 06:14:05', '2017-10-02 06:15:45', 1, 1, '192.168.100.10', '192.168.137.1', 1),
(2, 'Tips Membuat Soal Ujian dengan Rapi di Semua Perangkat', '<div class=\"article-content\">\n\n<p>Ujian Berbasis Komputer sangat berbeda dengan ujian konvensional. Jika soal ujian konvensional dicetak dengan ukuran yang pasti dan tetap, soal pada Ujian Berbasis Komputer akan ditampilkan pada perangkat elektronik dengan berbagai ukuran. Dengan demikian, pembuatan soal Ujian Bebasis Komputer tidak bisa sembarangan.</p>\n\n<p>Agar materi ujian dapat ditampilkan di semua perangkat secara responsif, ada beberapa hal yang perlu Anda ketahui.</p>\n\n<ol><li>Tulisan dalam format gambar tidak bisa menggulung sebagaimana format teks.</li>\n\n<li>Tidak semua gambar dapat menyesuaikan resolusi layar. Meskipun secara teknis dapat dibuat demikian, namun jika gambar tersebut berisi tulisan dan gambar tersebut ditampilkan dalam ukuran yang sangat kecil, maka tulisan pada gambar tidak dapat dibaca.</li>\n\n<li>Karakter tab tidak diakui pada HTML. Untuk kerapian, gunakan tabel sebagai pengganti.</li>\n\n<li>Lebar karakter dan spasi putih sangat tergantung kepada font.</li>\n\n</ol><p>Usahakan soal selalu dalam format teks agar mudah ditampilkan pada semua perangkat. Gunakan aplikasi OCR (Optical Character Recognition) untuk mengambil teks dari sebuah gambar atau file PDF. Software OCR gratis dapat didownload di&nbsp;<a href=\"http://www.paperfile.net/\">http://www.paperfile.net/</a>&nbsp;</p>\n\n<p>Ukuran gambar sebaiknya tidak terlalu besar dan usahakan tidak lebih dari 500 pixel. Potonglah gambar yang memiliki latar putih yang tidak diperlukan. Jika ukuran gambar terlalu besar, akan bermasalah jika soal ujian ditampilkan pada perangkat dengan ukuran kecil seperti tablet atau smartphone.</p>\n\n<p>Selain hal-hal yang disebutkan di atas, pengguna perlu sering-sering latihan untuk mendapatkan hasil yang baik. Memahami konsep web dan HTML akan lebih baik.</p>\n\n</div>', '2017-09-10 03:28:50', '2017-10-02 06:15:26', 1, 1, '192.168.100.12', '192.168.137.1', 1),
(3, 'Pemanfaatan Teknologi Digital untuk Pendidikan', '<div class=\"article-content\">\n\n<p>Perkembangan teknologi digital sangat pesat terutama sejak internet mulai dipublikasikan. Dalam satu bulan saja sudah ada penemuan baru yang akan menggantikan teknologi terdahulu. Perkembangan ini tidak dapat dibendung karena memang merupakan kebutuhan semua orang.</p>\n\n<p>Semua bidang termasuk bidang pendidikan juga terdampak oleh perkembangan tersebut. Apakah dampaknya akan baik atau buruk, tergantung kepada bagaimana kita menerimanya. Jika kita menerimanya dengan cara yang baik, maka akan baik pula dampaknya. Sebaliknya, jika kita menerimanya dengan cara yang buruk, maka akan buruk pula dampaknya. Akan tetapi, jika kita menolaknya, justru kita yang akan tertinggal oleh orang lain.</p>\n\n<p>Seyogyanya perkembangan teknologi digital ini dapat dimanfaatkan oleh guru, siswa, dan semua elemen masyarakat khususnya di bidang pendidikan. Teknologi digital akan menjadi sebuah media yang sangat fleksibel dan interaktif dalam penyampaian materi pendidikan. Selain untuk menyampaikan materi, teknologi digital juga dapat digunakan untuk menguji seberapa besar daya serap siswa terhadap materi tersebut.</p>\n\n</div>', '2017-09-10 03:30:54', '2017-10-02 06:15:14', 1, 1, '192.168.100.12', '192.168.137.1', 1),
(4, 'Belajar Menjadi Lebih Menyenangkan Bersama Planet Edu', '<div class=\"article-content\">\n\n\n<p style=\"text-align: justify;\">Planet Edu menjadi media pembelajaran yang menyenangkan bagi siswa dan guru. Siswa dan guru akan merasakanpengalaman baru dalam proses belajar mengajar serta ujian. Ujian menggunakan Planet Edu akan memudahkan guru menyelenggarakan ujian serta mengurangi penggunaan kertas. Kemungkinan jawaban siswa yang tercecer juga dapat dihindari. Selain itu, guru akan lebih objektif dan terbuka dalam memberikan nilai kepada siswa.</p>\n\n\n<p style=\"text-align: justify;\">Guru dapat menggunakan sebuah paket soal untuk ujian beberapa kelas dan sistem akan memilih secara acak soal yang tersedia dalam paket tersebut untuk diberikan kepada masing-masing peserta. Setiap siswa akan mendapatkan soal yang berbeda-beda sehingga kemungkinan kerjasama dalam ujian juga dapat dihindari. Selain soal yang berbeda-beda, urutan pilihan juga akan diacak setiap kali soal ditampilkan di layar sehingga akan menyulitkan siswa mencontek siswa lain yang kebetulan mendapatkan sebuah soal yang sama di nomor yang berbeda.</p>\n\n\n<p style=\"text-align: justify;\">Dengan menggunakan Planet Edu, siswa akan terbiasa mengerjakan ujian berbasis komputer atau UBK. Siswa tidak akan canggung lagi ketika menghadapi ujian nasional dan ujian lain yang menggunakan komputer sebagai medianya.</p>\n\n\n</div>', '2017-09-10 03:32:32', '2017-09-10 03:32:34', 1, 1, '192.168.100.12', '192.168.100.12', 1),
(5, 'Planet Edu Menyediakan Ribuan Soal Ujian untuk Sekolah', '<div class=\"article-content\">\n\n\n<p>Bagi guru yang tidak sempat membuat soal ujian untuk siswa-siswinya, Planet Edu menyediakan ribuan soal ujian berikut dengan pilihan dan kunci jawabannya. Soal-soal tersebut dapat dengan bebas diambil untuk diujikan kepada siswanya.</p>\n\n\n<p>Untuk sementara, Planet Edu baru menyediakan soal-soal untuk jenjang SMP dan SMA sederajat mengingat waktu UNBK semakin dekat. Soal-soal tersebut bersumber dari soal-soal Ujian Nasional yang dikumpulkan dari berbagai sumber. Beberapa soal berisi audio, gambar, tabel, persamaan, dan lain-lain.</p>\n\n\n<p>Guru dapat memilih soal yang akan diambil dan dapat pula mengubahnya. Dengan demikian, guru akan sangat terbantu dengan adanya bank soal tersebut.</p>\n\n\n<p>Sebelum mengambil soal ujian dari bank soal, pastikan jumlah pilihan pada bank soal sama dengan jumlah pilihan pada pengaturan ujian. Apabila guru ingin menerapkan sistem penalti, misalnya ketika jawaban benar diberi nilai 4 dan jika salah dikurangi 1, maka guru harus mengubah semua nilai pada setiap soal yang diimpor karena semua soal yang tersedia di bank soal hanya memiliki poin 1.</p>\n\n\n</div>', '2017-09-10 03:34:25', '2017-09-10 03:34:30', 1, 1, '192.168.100.12', '192.168.100.12', 1),
(6, 'Bank Soal Planet Edu', '<div class=\"article-content\">\n\n\n<p>Bagi sekolah yang telah terbiasa menggunakan aplikasi CBT atau <em>Computer-Based Test</em>, membuat soal ujian adalah hal yang sangat mudah meskipun soal tersebut mengandung gambar, grafik, video, audio, maupun persamaan. Akan tetapi, dengan keterbatasan waktu, tidak semua guru sempat membuat soal ujian. Oleh sebab itu Planet Edu menyediakan soal-soal ujian dalam bank soal.</p>\n\n\n<p>Bank soal adalah sebuah modul dari Planet Edu di mana administrator sekolah dan guru dapat mengambil soal-soal tersebut untuk dimasukkan ke dalam ujian yang telah atau akan dibuat. Planet Edu menyediakan ribuan soal-soal ujian dari berbagai mata pelajaran dan tingkat. Administrator sekolah dan guru dapat mengambil semua soal dalam paket yang dipilih dan dapat pula mengambil sebagian soal saja. Setelah soal tersebut disalin ke dalam ujian yang dipilih, administrator dan guru tetap dapat mengubah, menghapus atau menambah soal-soal ujian. Semakin banyak koleksi soal dalam sebuah ujian akan semakin baik karena kemungkinan siswa mendapatkan soal yang sama akan semakin kecil.</p>\n\n\n<p>Soal-soal ujian dalam bank soal hanya dapat dilihat oleh guru dan administraor sekolah. Siswa dan umum tidak dapat melihat soal tersebut sehingga soal kunci jawaban tidak akan bocor.</p>\n\n\n<p>Sangat diharapkan guru dan administrator sekolah membuat soal sendiri untuk memperkaya materi ujian karena kurikulum sekolah bergerak dinamis. Meskipun demikian, Planet Edu akan terus memperbarui dan menambah koleksi dalam bank soal untuk mempermudah guru dan administrator sekolah dalam membuat ujian.</p>\n\n\n</div>', '2017-09-10 03:34:42', '2017-09-26 06:12:45', 1, 1, '192.168.100.12', '192.168.0.40', 1),
(7, 'Aplikasi Computer Based Test Dari Planetbiru', '<p>Computer Based Test atau CBT selama ini selalu dipandang sebagai sebagai sesuatu yang sangat mahal karena harus menyediakan server, jaringan, komputer untuk digunakan oleh siswa, dan juga aplikasi ujian. Planetbiru membuat terobosan baru untuk meminimalisir biaya tersebut dengan menciptakan sebuah aplikasi CBT yang dikemas dalam sebuah mini server. Aplikasi ini bernama Planet Edu. Aplikasi ini dipaketkan dalam sebuah perangkat portable hemat energi. Tidak tanggung-tanggung, ukuran perangkat tersebut tidak jauh berbeda dengan kartu debit.</p>\n\n\n<p>Aplikasi ini dibandrol dengan harga yang sangat terjangkau oleh semua sekolah. Aplikasi yang tidak membutuhkan internet ini dapat langsung diakses dengan menggunakan laptop atau desktop. Dengan perangkat tambahan berupa wireless router, aplikasi ini dapat diakses oleh semua jenis laptop, tablet dan juga smartphone. Administrator, guru dan siswa dapat mengakses aplikasi ini melalui jaringan nirkabel atau wireless.</p>\n\n\n<p>Aplikasi ini diluncurkan bersamaan dengan hari ulang tahun media sosial <a href=\"http://www.planetbiru.com\">Planetbiru</a> ke 9 yang jatuh pada tanggal 14 Oktober 2017. Aplikasi ini dapat dibeli secara online dari toko resmi Planetbiru. Kepala sekolah, guru, orangtua bahkan siswa dapat membeli aplikasi ini.</p>\n\n\n<p>Aplikasi CBT Planet Edu dapat langsung digunakan dengan cara menyambungkannya dengan laptop atau desktop menggunakan kabel UTP. Jika pengguna ingin mengakses Planet Edu secara wireless, pengguna dapat menyambungkan Planet Edu dengan sebuah wireless router. Bagi pengguna yang menginginkan kemudahan, Planetbiru menyediakan paket Planet Edu dengan wireless router yang telah dikonfigurasi dan langsung bisa digunakan.</p>\n\n\n<p>Planetbiru menyediakan bank soal yang berisi ribuan contoh-contoh soal ujian untuk tingkat SMP dan SMA. Soal-soal tersebut sudah ada di dalam server Planet Edu dan dapat langsung digunakan oleh guru maupun administrator sekolah. Soal-soal tersebut terdiri dari soal-soal teks, soal bergambar, soal dengan rumus dan persamaan, serta soal dengan audio untuk listening.</p>', '2017-09-23 17:56:43', '2017-09-24 22:03:02', 1, 1, '192.168.0.40', '192.168.137.1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_mail_list`
--

CREATE TABLE `edu_mail_list` (
  `id` bigint(20) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `edu_member_school`
--

CREATE TABLE `edu_member_school` (
  `member_id` bigint(20) NOT NULL,
  `school_id` int(11) NOT NULL,
  `role` char(1) NOT NULL DEFAULT 'S',
  `class_id` bigint(20) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_member_school`
--

INSERT INTO `edu_member_school` (`member_id`, `school_id`, `role`, `class_id`, `time_create`, `active`) VALUES
(1, 1, 'A', NULL, '2023-01-20 08:52:42', 1),
(34, 1, 'S', 28, '2023-01-20 08:52:42', 1),
(35, 1, 'S', 29, '2023-01-20 08:52:42', 1),
(36, 1, 'S', 30, '2023-01-20 08:52:42', 1),
(37, 1, 'S', 31, '2023-01-20 08:52:42', 1),
(38, 1, 'S', 32, '2023-01-20 08:52:42', 1),
(39, 1, 'S', 33, '2023-01-20 08:52:42', 1),
(40, 1, 'S', 34, '2023-01-20 08:52:42', 1),
(41, 1, 'S', 17, '2023-01-20 08:52:42', 1),
(42, 1, 'S', 18, '2023-01-20 08:52:42', 1),
(43, 1, 'S', 19, '2023-01-20 08:52:42', 1),
(44, 1, 'S', 20, '2023-01-20 08:52:42', 1),
(45, 1, 'S', 21, '2023-01-20 08:52:42', 1),
(46, 1, 'S', 22, '2023-01-20 08:52:42', 1),
(47, 1, 'S', 23, '2023-01-20 08:52:42', 1),
(48, 1, 'S', 24, '2023-01-20 08:52:42', 1),
(49, 1, 'S', 1, '2023-01-20 08:52:42', 1),
(50, 1, 'S', 1, '2023-01-20 08:52:42', 1),
(51, 1, 'S', 1, '2023-01-20 08:52:42', 1),
(52, 1, 'S', 1, '2023-01-20 08:52:42', 1),
(53, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(54, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(55, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(56, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(57, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(58, 1, 'S', 2, '2023-01-20 08:52:42', 1),
(59, 1, 'S', 9, '2023-01-20 08:52:42', 1),
(60, 1, 'S', 9, '2023-01-20 08:52:42', 1),
(61, 1, 'S', 9, '2023-01-20 08:52:42', 1),
(62, 1, 'S', 10, '2023-01-20 08:52:42', 1),
(63, 1, 'S', 10, '2023-01-20 08:52:42', 1),
(64, 1, 'S', 10, '2023-01-20 08:52:42', 1),
(65, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(66, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(67, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(68, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(69, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(70, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(71, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(72, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(73, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(74, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(75, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(76, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(77, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(78, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(79, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(80, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(81, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(82, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(83, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(84, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(85, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(86, 1, 'T', NULL, '2023-01-20 08:52:42', 1),
(87, 1, 'T', NULL, '2023-01-20 08:52:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_option`
--

CREATE TABLE `edu_option` (
  `option_id` bigint(20) NOT NULL,
  `question_id` bigint(20) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `score` double DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `member_create` varchar(20) DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `member_edit` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_option`
--

INSERT INTO `edu_option` (`option_id`, `question_id`, `content`, `order`, `score`, `time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
(1, 1, 'qwfqwf', 1, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(2, 1, 'wefewf', 2, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(3, 1, 'qwgqiwur', 3, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(4, 1, 'fqiwf', 4, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(5, 2, 'qwfiuqwfq', 1, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(6, 2, 'qwfqwf', 2, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(7, 2, 'efqwifuqwf', 3, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(8, 2, 'efewfiewfw', 4, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(9, 3, '1', 1, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(10, 3, '2', 2, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(11, 3, '3', 3, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(12, 3, '4', 4, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(13, 4, '9', 1, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(14, 4, '8', 2, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(15, 4, '7', 3, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(16, 4, '3', 4, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(17, 5, 'Al An \'Am', 1, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(18, 5, 'Al Fatihah', 2, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(19, 5, 'An Naas', 3, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(20, 5, 'Ali Imron', 4, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(21, 6, 'Trigonometri', 1, 1, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(22, 6, 'Pytagoras', 2, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(23, 6, 'Molekul', 3, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(24, 6, 'Persamaan gerak', 4, 0, '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_question`
--

CREATE TABLE `edu_question` (
  `question_id` bigint(20) NOT NULL,
  `basic_competence` varchar(20) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `test_id` bigint(20) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `multiple_choice` tinyint(1) DEFAULT 0,
  `random` tinyint(1) DEFAULT 0,
  `numbering` varchar(50) DEFAULT 'upper-alpha',
  `digest` varchar(40) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `member_create` varchar(50) DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `member_edit` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_question`
--

INSERT INTO `edu_question` (`question_id`, `basic_competence`, `content`, `test_id`, `order`, `multiple_choice`, `random`, `numbering`, `digest`, `time_create`, `member_create`, `time_edit`, `member_edit`, `active`) VALUES
(1, '3.9', '<p>sin(x/2) + cos(4x)</p>\r\n<p><img class=\"ascii-svg\" src=\"lib.tools/asciisvg/svgimg.php?sscr=-10%2C10%2C-5%2C5%2C1%2C1%2C1%2C1%2C1%2C500%2C200%2Cfunc%2Csin(2x)%20%2B%20cos(3.4x)%2Cnull%2C0%2C0%2C-12%2C12%2Cblue%2C1%2C5%202\" style=\"width: 500px; height: 200px; vertical-align: middle; float: none;\" data-sscr=\"-10%2C10%2C-5%2C5%2C1%2C1%2C1%2C1%2C1%2C500%2C200%2Cfunc%2Csin(2x)%20%2B%20cos(3.4x)%2Cnull%2C0%2C0%2C-12%2C12%2Cblue%2C1%2C5%202\"></p>', 1, 1, 1, 1, 'upper-alpha', '0059fc751be50aba19064bf74010d545', '2023-01-22 11:04:24', '1', '2023-01-22 14:06:31', '1', 1),
(2, '', 'ehfuihefhihfiwef', 1, 2, 1, 1, 'upper-alpha', '539c3edbc9a66a154df7814205b0f6dc', '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(3, '6.9', '<p>Perhatikan Persamaan berikut:<br> Disediakan persamaan egfiuwef wefuwegfugweuifweufwuegfuwgefuweufwuef<br> <img class=\"latex-image\" style=\"vertical-align: middle;\" alt=\"f(x)=a_0+\\sum_(n=1)^&infin; (a_n  \\cos (n&pi;x/L)+b_n  \\sin (n&pi;x/L) )\" data-latex=\"f(x)=a_0+\\sum_(n=1)^&infin; (a_n  \\cos (n&pi;x/L)+b_n  \\sin (n&pi;x/L) )\" src=\"media.edu/school/1/test/1/35310254dca906659d2a9f8d4c3074f9.png\"></p>\r\n<p><img class=\"latex-image\" style=\"vertical-align: middle;\" alt=\"f(x)=a_0+\\sum_(n=1)^&infin; (a_n  \\cos (n&pi;x/L)+b_n  \\sin (n&pi;x/L) )\" data-latex=\"f(x)=a_0+\\sum_(n=1)^&infin; (a_n  \\cos (n&pi;x/L)+b_n  \\sin (n&pi;x/L) )\" src=\"media.edu/school/1/test/1/35310254dca906659d2a9f8d4c3074f9.png\"></p>\r\n<p><img class=\"latex-image\" style=\"vertical-align: middle;\" alt=\"\\sin &alpha;&plusmn;\\sin &beta;=2 \\sin (1/2 (&alpha;&plusmn;&beta;))  \\cos (1/2 (&alpha;&#8723;&beta;))\" data-latex=\"\\sin &alpha;&plusmn;\\sin &beta;=2 \\sin (1/2 (&alpha;&plusmn;&beta;))  \\cos (1/2 (&alpha;&#8723;&beta;))\" src=\"media.edu/school/1/test/1/2cd73b7c7fb616ad84734ca592c4e928.png\"><br> Nilai x pada persamaan berikut adalah:</p>', 1, 3, 1, 1, 'upper-alpha', '1d9f61dcd376cf2c21a8c70bce18493e', '2023-01-22 11:04:24', '1', '2023-01-22 14:12:52', '1', 1),
(4, NULL, 'Perhatikan persamaan berikut:<br />\r\n<img src=\"media.edu/school/1/test/1/88c5ededa8714ba3126dc77128274094.png\" alt=\"88c5ededa8714ba3126dc77128274094.png\" style=\"vertical-align:middle\" data-latex=\"(1+x)^n=1+nx/1!+(n(n-1) x^2)/2!+&#8943;\" class=\"latex-image\" alt=\"(1+x)^n=1+nx/1!+(n(n-1) x^2)/2!+&#8943;\"><br />\r\nNila x adalah...', 1, 4, 1, 1, 'upper-alpha', 'ee71b9be40426a51fe8fa698f5bf631b', '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(5, NULL, 'Berikut adalah ayat Al Quran<br />\r\n&#1589;&#1616;&#1585;&#1614;&#1575;&#1591;&#1614; &#1575;&#1604;&#1614;&#1617;&#1584;&#1616;&#1610;&#1606;&#1614; &#1571;&#1614;&#1606;&#1618;&#1593;&#1614;&#1605;&#1618;&#1578;&#1614; &#1593;&#1614;&#1604;&#1614;&#1610;&#1618;&#1607;&#1616;&#1605;&#1618; &#1594;&#1614;&#1610;&#1618;&#1585;&#1616; &#1575;&#1604;&#1618;&#1605;&#1614;&#1594;&#1618;&#1590;&#1615;&#1608;&#1576;&#1616; &#1593;&#1614;&#1604;&#1614;&#1610;&#1618;&#1607;&#1616;&#1605;&#1618; &#1608;&#1614;&#1604;&#1575; &#1575;&#1604;&#1590;&#1614;&#1617;&#1575;&#1604;&#1616;&#1617;&#1610;&#1606;&#1614;<br />\r\nAyat  tersebut terdapat pada surat', 1, 5, 1, 1, 'upper-alpha', '401e6684386db582eb33d0498ab9ab70', '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1),
(6, NULL, 'Perhatikan persamaan berikut:<br />\r\n<img src=\"media.edu/school/1/test/1/089c05793a35a91e8a251e8da7265ee1.png\" alt=\"089c05793a35a91e8a251e8da7265ee1.png\" style=\"vertical-align:middle\" data-latex=\"\\sin &alpha;&plusmn;\\sin &beta;=2 \\sin (1/2 (&alpha;&plusmn;&beta;))  \\cos (1/2 (&alpha;&#8723;&beta;))\" class=\"latex-image\" alt=\"\\sin &alpha;&plusmn;\\sin &beta;=2 \\sin (1/2 (&alpha;&plusmn;&beta;))  \\cos (1/2 (&alpha;&#8723;&beta;))\"><br />\r\nPersamaan di atas adalah:', 1, 6, 1, 1, 'upper-alpha', '5de10803c3facf745dd581c66f9143c4', '2023-01-22 11:04:24', '1', '2023-01-22 11:04:24', '1', 1);

--
-- Triggers `edu_question`
--
DELIMITER $$
CREATE TRIGGER `after_delete_edu_question` AFTER DELETE ON `edu_question` FOR EACH ROW begin
DELETE FROM `edu_option` where `edu_option`.`question_id` = OLD.`question_id`;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `edu_school`
--

CREATE TABLE `edu_school` (
  `school_id` bigint(20) NOT NULL,
  `school_code` varchar(100) DEFAULT NULL,
  `token_school` varchar(32) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `school_type_id` varchar(2) DEFAULT NULL,
  `school_grade_id` enum('1','2','3','4','5','6') DEFAULT NULL,
  `public_private` enum('U','I') DEFAULT 'U',
  `open` tinyint(1) DEFAULT 0,
  `principal` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `country_id` varchar(2) DEFAULT NULL,
  `state_id` varchar(20) DEFAULT NULL,
  `city_id` varchar(20) DEFAULT NULL,
  `student` int(11) DEFAULT NULL,
  `prevent_change_school` tinyint(1) DEFAULT 0,
  `prevent_resign` tinyint(1) DEFAULT 0,
  `use_token` tinyint(1) DEFAULT 0,
  `time_import_first` datetime DEFAULT NULL,
  `time_import_last` datetime DEFAULT NULL,
  `admin_import_first` bigint(20) DEFAULT NULL,
  `admin_import_last` bigint(20) DEFAULT NULL,
  `ip_import_first` varchar(45) DEFAULT NULL,
  `ip_import_last` varchar(45) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_school`
--

INSERT INTO `edu_school` (`school_id`, `school_code`, `token_school`, `name`, `description`, `school_type_id`, `school_grade_id`, `public_private`, `open`, `principal`, `address`, `phone`, `email`, `language`, `country_id`, `state_id`, `city_id`, `student`, `prevent_change_school`, `prevent_resign`, `use_token`, `time_import_first`, `time_import_last`, `admin_import_first`, `admin_import_last`, `ip_import_first`, `ip_import_last`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
(1, 'sma-planet-edu-jakarta', '491987b1069ed8caf7f045d5b0001076', 'SMA Planet Edu Jakarta', '<p><img class=\"equation-image\" style=\"vertical-align: middle;\" alt=\"\\sqrt[\\left(\\frac{2}{x}\\right)]{\\frac{2}{\\left(\\frac{23x}{x}\\right)}}\" data-equation=\"%7B%22latex%22%3A%22%5C%5Csqrt%5B%5C%5Cleft(%5C%5Cfrac%7B2%7D%7Bx%7D%5C%5Cright)%5D%7B%5C%5Cfrac%7B2%7D%7B%5C%5Cleft(%5C%5Cfrac%7B23x%7D%7Bx%7D%5C%5Cright)%7D%7D%22%2C%22json%22%3A%7B%22type%22%3A%22Equation%22%2C%22value%22%3Anull%2C%22operands%22%3A%7B%22topLevelContainer%22%3A%5B%7B%22type%22%3A%22NthRoot%22%2C%22value%22%3Anull%2C%22operands%22%3A%7B%22radicand%22%3A%5B%7B%22type%22%3A%22StackedFraction%22%2C%22value%22%3Anull%2C%22operands%22%3A%7B%22numerator%22%3A%5B%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%222%22%2C%22operands%22%3Anull%7D%5D%2C%22denominator%22%3A%5B%7B%22type%22%3A%22BracketPair%22%2C%22value%22%3A%22parenthesisBracket%22%2C%22operands%22%3A%7B%22bracketedExpression%22%3A%5B%7B%22type%22%3A%22StackedFraction%22%2C%22value%22%3Anull%2C%22operands%22%3A%7B%22numerator%22%3A%5B%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%222%22%2C%22operands%22%3Anull%7D%2C%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%223%22%2C%22operands%22%3Anull%7D%2C%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%22x%22%2C%22operands%22%3Anull%7D%5D%2C%22denominator%22%3A%5B%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%22x%22%2C%22operands%22%3Anull%7D%5D%7D%7D%5D%7D%7D%5D%7D%7D%5D%2C%22degree%22%3A%5B%7B%22type%22%3A%22BracketPair%22%2C%22value%22%3A%22parenthesisBracket%22%2C%22operands%22%3A%7B%22bracketedExpression%22%3A%5B%7B%22type%22%3A%22StackedFraction%22%2C%22value%22%3Anull%2C%22operands%22%3A%7B%22numerator%22%3A%5B%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%222%22%2C%22operands%22%3Anull%7D%5D%2C%22denominator%22%3A%5B%7B%22type%22%3A%22Symbol%22%2C%22value%22%3A%22x%22%2C%22operands%22%3Anull%7D%5D%7D%7D%5D%7D%7D%5D%7D%7D%5D%7D%7D%7D\" src=\"media.edu/school/1/description/167967e13d113d635b5e0bb118dc0bd3.svg\"></p>', NULL, '5', 'U', 0, 'Drs. Mukidi', 'JAKARTA', '0210000011', '', 'id', 'ID', NULL, NULL, NULL, 1, 1, 0, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_school_program`
--

CREATE TABLE `edu_school_program` (
  `school_program_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `default` bigint(20) DEFAULT 0,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `active` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_school_program`
--

INSERT INTO `edu_school_program` (`school_program_id`, `school_id`, `name`, `order`, `default`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `active`) VALUES
(1, 1, 'IPA', NULL, 0, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1),
(2, 1, 'IPS', NULL, 0, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `edu_school_response`
--

CREATE TABLE `edu_school_response` (
  `school_response_id` bigint(20) NOT NULL,
  `school` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `edu_student`
--

CREATE TABLE `edu_student` (
  `student_id` bigint(20) NOT NULL,
  `token_student` varchar(32) DEFAULT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `reg_number` varchar(20) DEFAULT NULL,
  `reg_number_national` varchar(20) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `class_id` bigint(20) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('M','W') DEFAULT 'M',
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `password_initial` varchar(45) DEFAULT NULL,
  `auth` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country_id` bigint(20) DEFAULT NULL,
  `state_id` bigint(20) DEFAULT NULL,
  `city_id` bigint(20) DEFAULT NULL,
  `religion_id` varchar(2) DEFAULT NULL,
  `prevent_change_school` tinyint(1) DEFAULT 0,
  `prevent_resign` tinyint(1) DEFAULT 0,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_student`
--

INSERT INTO `edu_student` (`student_id`, `token_student`, `school_id`, `reg_number`, `reg_number_national`, `grade_id`, `class_id`, `username`, `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `password`, `password_initial`, `auth`, `address`, `country_id`, `state_id`, `city_id`, `religion_id`, `prevent_change_school`, `prevent_resign`, `time_create`, `time_edit`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `blocked`, `active`) VALUES
(34, 'ce8da5468da1962ccd56bc8957909b90', 1, '1111111', '1011199', 12, 28, 'Budi Raharjo', 'Budi Raharjo', 'M', 'JAKARTA', '2135-11-17', '08123456781', 'st_1011199_1@planetbiru.com', '593024eb40414ef9ec82f4560a6c31bb', '5468da', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(35, 'b82de42e8d97dd945de3cdfa0f276ac7', 1, '1111112', '1011200', 12, 29, 'Muhammad Ramadhan', 'Muhammad Ramadhan', 'M', 'JAKARTA', '2135-11-18', '08123456782', 'st_1011200_1@planetbiru.com', 'c01806a97bcbb7cca8c23e2559117567', '42e8d9', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(36, 'ade319812826e53cb197f07f71160c4b', 1, '1111113', '1011201', 12, 30, 'Agung Ramadhan', 'Agung Ramadhan', 'M', 'JAKARTA', '2135-11-19', '08123456783', 'st_1011201_1@planetbiru.com', 'fabdeb91fd922f7c8eb3494e8ec57e33', '981282', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(37, '8772eac8524dbebd0142327e89fb9db1', 1, '1111114', '1011202', 12, 31, 'Sinta Anjani', 'Sinta Anjani', 'W', 'JAKARTA', '2135-11-20', '08123456784', 'st_1011202_1@planetbiru.com', '60e73647bff21ab8bf425bf3fbe98ecd', 'ac8524', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(38, '91439544817d1d7d7a45e0fb2ebf5506', 1, '1111115', '1011203', 12, 32, 'Siviasari', 'Siviasari', 'W', 'JAKARTA', '2135-11-21', '08123456785', 'st_1011203_1@planetbiru.com', 'ede8a1181837641fa8e4517b35ef0baa', '544817', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(39, 'ffd48de8793515165a6e255365f6def1', 1, '1111116', '1011204', 12, 33, 'Veni Ananda', 'Veni Ananda', 'W', 'BANDUNG', '2135-11-22', '08123456786', 'st_1011204_1@planetbiru.com', 'f7fa364975f44cce9321dff3760ea377', 'de8793', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(40, 'a78f3001bf7b05a6ed55b83cdde98fcb', 1, '1111117', '1011205', 12, 34, 'Mukhlis', 'Mukhlis', 'M', 'BANDUNG', '2135-11-23', '08123456787', 'st_1011205_1@planetbiru.com', '15bdac02e0b283acc24b74103110dad0', '001bf7', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(41, '7c86d4373d9e0f5a1689cc85d409fafb', 1, '1111118', '1011206', 11, 17, 'Bimantara', 'Bimantara', 'M', 'BANDUNG', '2136-11-24', '08123456788', 'st_1011206_1@planetbiru.com', '876f5e00a10a9461299de4cf3822ded8', '4373d9', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(42, 'e995f4fe99ddaf5d211e296f421092e5', 1, '1111119', '1011207', 11, 18, 'Dewi Permatasari', 'Dewi Permatasari', 'W', 'SURABAYA', '2136-11-25', '08123456789', 'st_1011207_1@planetbiru.com', '95c7bdb937eb4d5b1fb2a11c9962afb4', '4fe99d', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(43, '102867ddcb9e9f53aba8d213e139fab6', 1, '1111120', '1011208', 11, 19, 'Jaka Umbara', 'Jaka Umbara', 'M', 'BEKASI', '2136-11-26', '08123456790', 'st_1011208_1@planetbiru.com', '8ad975cc47ee53a1d10e694b5c06426a', '7ddcb9', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(44, 'eab54981805da57e2e596c91f29eb32d', 1, '1111121', '1011209', 11, 20, 'Nona Melati', 'Nona Melati', 'W', 'JAKARTA', '2136-11-27', '08123456791', 'st_1011209_1@planetbiru.com', '8dd2706a358ba9fc9a55844886591272', '981805', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(45, '16e30a1c014f7f23495219bab0b5770e', 1, '1111122', '1011210', 11, 21, 'Ardianto', 'Ardianto', 'M', 'JAKARTA', '2136-11-28', '08123456792', 'st_1011210_1@planetbiru.com', '4f9d01d8b3fcdf76bfd1bf4c01781552', 'a1c014', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(46, 'bcfd8a0621781adc2d90d59a606552b0', 1, '1111123', '1011211', 11, 22, 'Angga Permana', 'Angga Permana', 'M', 'BANDUNG', '2136-11-29', '08123456793', 'st_1011211_1@planetbiru.com', 'ea6c1a8f5d8bd671ae5f92afdb3190e1', 'a06217', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(47, '6b549671ce2147d40c7468e52777b71a', 1, '1111124', '1011212', 11, 23, 'Ratnasari', 'Ratnasari', 'W', 'BANDUNG', '2136-11-30', '08123456794', 'st_1011212_1@planetbiru.com', '58159cca9547b1c83e8b1d6dceb3f38c', '671ce2', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(48, '91af07bdc53834889210b66ae5c2abd6', 1, '1111125', '1011213', 11, 24, 'Anissa Widiya', 'Anissa Widiya', 'W', 'BANDUNG', '2136-12-01', '08123456795', 'st_1011213_1@planetbiru.com', '4760491837a95dc7002e02568f8944bf', '7bdc53', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(49, 'eb34b3a7dc466f4b73600e631b523580', 1, '1111126', '1011214', 10, 1, 'Caca Melani', 'Caca Melani', 'W', 'SURABAYA', '2137-12-02', '08123456796', 'st_1011214_1@planetbiru.com', '3363676ce59ecc75cf7346f26d0b6004', '3a7dc4', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(50, 'c1d3db35ac976f343e78cb3ca80c99fc', 1, '1111127', '1011215', 10, 1, 'Tiara Permatasari', 'Tiara Permatasari', 'W', 'BEKASI', '2137-12-03', '08123456797', 'st_1011215_1@planetbiru.com', 'ffe59b17137e28ab147f885fd6776b43', 'b35ac9', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(51, 'bd1180aa2db7e6c961eef5afe6ff7a19', 1, '1111128', '1011216', 10, 1, 'Cesilya', 'Cesilya', 'W', 'JAKARTA', '2137-12-04', '08123456798', 'st_1011216_1@planetbiru.com', 'aa2fbde2108057b06d775f9d30e6ebd4', '0aa2db', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(52, '83b982e68ef3d9a0a2430d9748d7c813', 1, '1111129', '1011217', 10, 1, 'Paramitha', 'Paramitha', 'W', 'JAKARTA', '2137-12-05', '08123456799', 'st_1011217_1@planetbiru.com', 'c20e84fab5ad9975d5389847139eeed3', '2e68ef', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(53, '6bdb901cf9c4781098b99a1d0b3ba147', 1, '1111130', '1011218', 10, 2, 'Nina Kurniati', 'Nina Kurniati', 'W', 'BANDUNG', '2137-12-06', '08123456800', 'st_1011218_1@planetbiru.com', 'ec0d0791a5955f36a6e2a962cadc59c7', '01cf9c', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(54, 'f6a25c9f48e0364ff3870bf6ba4552c9', 1, '1111131', '1011219', 10, 2, 'Zahra Amanda', 'Zahra Amanda', 'W', 'BANDUNG', '2137-12-07', '08123456801', 'st_1011219_1@planetbiru.com', '26df70307e38ff9a456dec76a05a2946', 'c9f48e', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(55, 'b0422477d19ea45456ec2898715972c6', 1, '1111132', '1011220', 10, 2, 'Maulinda', 'Maulinda', 'W', 'BANDUNG', '2137-12-08', '08123456802', 'st_1011220_1@planetbiru.com', '4d60898436031ac660d083a82403e15d', '477d19', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(56, 'dd273cb3e4ef7a563039ee474ebbeec8', 1, '1111133', '1011221', 10, 2, 'Dadang Sunarto', 'Dadang Sunarto', 'M', 'SURABAYA', '2137-12-09', '08123456803', 'st_1011221_1@planetbiru.com', '67f366dfc3ef17d172b833569f30c346', 'cb3e4e', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(57, 'eb058240d2296fb2d8558c466a194b8e', 1, '1111134', '1011222', 10, 2, 'Didi Suyadi', 'Didi Suyadi', 'M', 'BEKASI', '2137-12-10', '08123456804', 'st_1011222_1@planetbiru.com', '07d6de2b356e2ffb378fd00382984f49', '240d22', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(58, '59abb139f60d2ab7faf93bdefda3e8ee', 1, '1111135', '1011223', 10, 2, 'Linda', 'Linda', 'W', 'BEKASI', '2137-12-11', '08123456805', 'st_1011223_1@planetbiru.com', 'f0559eb7a9f24bec5d3f34ca975c0c2d', '139f60', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(59, '60f8ef6402f5e2e3c482c8468fbed4f9', 1, '1111136', '1011224', 10, 9, 'Sri Nurhayati', 'Sri Nurhayati', 'W', 'BEKASI', '2137-12-12', '08123456806', 'st_1011224_1@planetbiru.com', '6b42e5e8910c03d942cdb38e74b7bd11', 'f6402f', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(60, 'a018554cb46de7a9d681651f4a1c918d', 1, '1111137', '1011225', 10, 9, 'Dini', 'Dini', 'W', 'BEKASI', '2137-12-13', '08123456807', 'st_1011225_1@planetbiru.com', '2d786e3c019fcd93b5fc37142285194d', '54cb46', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(61, 'beed695910c9dcfa33c2c20d538201d6', 1, '1111138', '1011226', 10, 9, 'Dina', 'Dina', 'W', 'BANDUNG', '2137-12-14', '08123456808', 'st_1011226_1@planetbiru.com', '5e322c279476dab1e1f4b64897754d79', '95910c', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(62, 'b2f69901a36456a4418f9247d628abde', 1, '1111139', '1011227', 10, 10, 'Winda', 'Winda', 'W', 'JAKARTA', '2137-12-15', '08123456809', 'st_1011227_1@planetbiru.com', '48d03866c7113399e2f8f29d3edb9d14', '901a36', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(63, 'c04ac47724f4786d639282df3f06bef0', 1, '1111140', '1011228', 10, 10, 'Tamara', 'Tamara', 'W', 'JAKARTA', '2137-12-16', '08123456810', 'st_1011228_1@planetbiru.com', '346760678fbf99f01334e6990a9ebf8f', '47724f', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1),
(64, '4d8227ff15165d555716ba8bde37e9dd', 1, '1111141', '1011229', 10, 10, 'Clarissa', 'Clarissa', 'W', 'JAKARTA', '2137-12-17', '08123456811', 'st_1011229_1@planetbiru.com', '909bad3b77d4ddd0968c240d589bf5fa', '7ff151', NULL, '', NULL, NULL, NULL, NULL, 1, 1, '2023-01-20 08:52:42', '2023-01-20 08:52:42', 1, 1, '127.0.0.1', '127.0.0.1', 0, 1);

--
-- Triggers `edu_student`
--
DELIMITER $$
CREATE TRIGGER `after_delete_edu_student` AFTER DELETE ON `edu_student` FOR EACH ROW begin
DELETE FROM `edu_member_school` where `role` = 'S' and `member_id` = OLD.`student_id`;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `edu_teacher`
--

CREATE TABLE `edu_teacher` (
  `teacher_id` bigint(20) NOT NULL,
  `token_teacher` varchar(32) DEFAULT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `reg_number` varchar(20) DEFAULT NULL,
  `reg_number_national` varchar(20) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('M','W') DEFAULT 'M',
  `birth_place` varchar(100) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `country_id` bigint(20) DEFAULT NULL,
  `state_id` bigint(20) DEFAULT NULL,
  `city_id` bigint(20) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `password_initial` varchar(45) DEFAULT NULL,
  `auth` varchar(45) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `religion_id` varchar(2) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `time_last_activity` datetime DEFAULT NULL,
  `admin_create` bigint(20) DEFAULT NULL,
  `admin_edit` bigint(20) DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `ip_last_activity` varchar(40) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_teacher`
--

INSERT INTO `edu_teacher` (`teacher_id`, `token_teacher`, `school_id`, `reg_number`, `reg_number_national`, `username`, `name`, `gender`, `birth_place`, `birth_day`, `phone`, `email`, `country_id`, `state_id`, `city_id`, `password`, `password_initial`, `auth`, `address`, `religion_id`, `time_create`, `time_edit`, `time_last_activity`, `admin_create`, `admin_edit`, `ip_create`, `ip_edit`, `ip_last_activity`, `blocked`, `active`) VALUES
(65, '4c2599a334cca3d79a4228030a6c9601', 1, '10001001001', '29010101001', 'Umar Bakri', 'Umar Bakri', 'M', 'JAKARTA', '2128-12-18', '0861131313', 'tc_29010101001_1@planetbiru.com', NULL, NULL, NULL, '850c5086ca0dd443d136d6555ed8abee', '9a334c', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(66, 'f60b19f9b5acb0ced9d94932e15b8b9f', 1, '10001001002', '29010101002', 'Mashudi', 'Mashudi', 'M', 'JAKARTA', '2128-12-19', '0861131314', 'tc_29010101002_1@planetbiru.com', NULL, NULL, NULL, '5481815587456e07c6d5697dbee8b3e8', '9f9b5a', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(67, 'b7ac10af7d380512666c3bbfec25c4f2', 1, '10001001003', '29010101003', 'Bara Api', 'Bara Api', 'M', 'JAKARTA', '2128-12-20', '0861131315', 'tc_29010101003_1@planetbiru.com', NULL, NULL, NULL, '5fc8f7fd09a91044c8cd55e25f5d4923', '0af7d3', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(68, '3b3342402f86fd8047adc48e104978bd', 1, '10001001004', '29010101004', 'Widya', 'Widya', 'W', 'JAKARTA', '2128-12-21', '0861131316', 'tc_29010101004_1@planetbiru.com', NULL, NULL, NULL, 'bf3cd8a0fbb0cdc26a8bbb36d3f022ef', '2402f8', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(69, 'c1b10c3cd2b89c22a38324665e569b7d', 1, '10001001005', '29010101005', 'Sudirman', 'Sudirman', 'M', 'JAKARTA', '2128-12-22', '0861131317', 'tc_29010101005_1@planetbiru.com', NULL, NULL, NULL, 'a56cf89a870b1d8a3d20ae566ce0c63a', 'c3cd2b', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(70, '8f7ba3e975daa54674da99b2dc68bc8e', 1, '10001001006', '29010101006', 'Mayang', 'Mayang', 'W', 'BANDUNG', '2128-12-23', '0861131318', 'tc_29010101006_1@planetbiru.com', NULL, NULL, NULL, 'c503f9d733f38cfc215b5fd3204ad8b2', '3e975d', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(71, '0f7e925a1763687c0dc423106000de96', 1, '10001001007', '29010101007', 'Renda', 'Renda', 'W', 'BANDUNG', '2128-12-24', '0861131319', 'tc_29010101007_1@planetbiru.com', NULL, NULL, NULL, '1904d00e7751aaca05a43591f2a1cca3', '25a176', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(72, '2509b9787f31922ca1a447c78611bdc1', 1, '10001001008', '29010101008', 'Raudah', 'Raudah', 'W', 'BANDUNG', '2128-12-25', '0861131320', 'tc_29010101008_1@planetbiru.com', NULL, NULL, NULL, '6530c2748973121696bb69af1e50e0cb', '9787f3', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(73, '9c260ae70a81714019e04cf2f84f4031', 1, '10001001009', '29010101009', 'Mutiara', 'Mutiara', 'W', 'SURABAYA', '2128-12-26', '0861131321', 'tc_29010101009_1@planetbiru.com', NULL, NULL, NULL, '0edc3e24fafbdc2d4f1f5e35bef0c7de', 'ae70a8', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(74, '3667b052cc1c27a4ca688d87db824390', 1, '10001001010', '29010101010', 'Buladi', 'Buladi', 'M', 'BEKASI', '2128-12-27', '0861131322', 'tc_29010101010_1@planetbiru.com', NULL, NULL, NULL, '2a18018263081bfea9cba7283a9d7d0a', '052cc1', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(75, '84cab755cd18c9e7d90410bc7f1607f8', 1, '10001001011', '29010101011', 'Sumijan', 'Sumijan', 'M', 'JAKARTA', '2128-12-28', '0861131323', 'tc_29010101011_1@planetbiru.com', NULL, NULL, NULL, 'c72f6d4908f14d875fee1c2c44cb98a0', '755cd1', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(76, 'cb11b868867273dcc5c78bb238c05263', 1, '10001001012', '29010101012', 'Mardianto', 'Mardianto', 'M', 'JAKARTA', '2128-12-29', '0861131324', 'tc_29010101012_1@planetbiru.com', NULL, NULL, NULL, '352256378cb8253ae2abdd6b985c8951', '868867', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(77, 'cf221fa56145f7f612431877da39d229', 1, '10001001013', '29010101013', 'Junaidi', 'Junaidi', 'M', 'BANDUNG', '2128-12-30', '0861131325', 'tc_29010101013_1@planetbiru.com', NULL, NULL, NULL, '24766814f9c995d89f19c8582906ae36', 'fa5614', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(78, '028d6be65225c512637ca8322e5e6928', 1, '10001001014', '29010101014', 'Aisyah', 'Aisyah', 'W', 'BANDUNG', '2128-12-31', '0861131326', 'tc_29010101014_1@planetbiru.com', NULL, NULL, NULL, '8095a7cf74acbe2c15d74427eeb0d4ad', 'be6522', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(79, 'aaabba561f67765faffff639e4dff816', 1, '10001001015', '29010101015', 'Mawardi', 'Mawardi', 'M', 'BANDUNG', '2129-01-01', '0861131327', 'tc_29010101015_1@planetbiru.com', NULL, NULL, NULL, '3e243ba18f016329f69db42448fdbc70', 'a561f6', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(80, 'c41167640765b75a4529b8f23da70c13', 1, '10001001016', '29010101016', 'Muladi', 'Muladi', 'M', 'SURABAYA', '2129-01-02', '0861131328', 'tc_29010101016_1@planetbiru.com', NULL, NULL, NULL, 'bfb219968d9256814ce70fc62f140977', '764076', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(81, 'b264589b845073badc9697a6ba6d09c5', 1, '10001001017', '29010101017', 'Srikandi', 'Srikandi', 'W', 'BEKASI', '2129-01-03', '0861131329', 'tc_29010101017_1@planetbiru.com', NULL, NULL, NULL, '1ec7172029f44958150b195642c832e7', '89b845', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(82, '9c468d355afaa271f3830d0c47e1d30c', 1, '10001001018', '29010101018', 'Bambang', 'Bambang', 'M', 'JAKARTA', '2129-01-04', '0861131330', 'tc_29010101018_1@planetbiru.com', NULL, NULL, NULL, '9e1c01102551c8fd1f7b46a244070a94', 'd355af', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(83, '2dea44dfc5df295e15856da64ecc7b88', 1, '10001001019', '29010101019', 'Markisa', 'Markisa', 'W', 'JAKARTA', '2129-01-05', '0861131331', 'tc_29010101019_1@planetbiru.com', NULL, NULL, NULL, '046af22180d2d536463dc2874154cc61', '4dfc5d', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(84, 'b0f7077ce6670ab6616417f763c4aa16', 1, '10001001020', '29010101020', 'Mawar', 'Mawar', 'W', 'BANDUNG', '2129-01-06', '0861131332', 'tc_29010101020_1@planetbiru.com', NULL, NULL, NULL, '5fbfe33ab64daf9a3acaa7b67ff3297b', '77ce66', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(85, '9bd61fbfb8122075c56feee1f5f81c72', 1, '10001001021', '29010101021', 'Rukman', 'Rukman', 'M', 'BANDUNG', '2129-01-07', '0861131333', 'tc_29010101021_1@planetbiru.com', NULL, NULL, NULL, '7117c7691a2c5e50dc88da71bbf06d00', 'fbfb81', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(86, '6225c816f762d5b7066b9891e0862521', 1, '10001001022', '29010101022', 'Dahlan', 'Dahlan', 'M', 'BANDUNG', '2129-01-08', '0861131334', 'tc_29010101022_1@planetbiru.com', NULL, NULL, NULL, 'ac8a417ec08e732daa74f688056cd0d7', '816f76', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1),
(87, '8f803b6684663825ee6604e9a6acf715', 1, '10001001023', '29010101023', 'Subekti', 'Subekti', 'M', 'SURABAYA', '2129-01-09', '0861131335', 'tc_29010101023_1@planetbiru.com', NULL, NULL, NULL, '306b7639d65fed66cfa86b253dbafcbe', 'b66846', NULL, '', NULL, '2023-01-20 08:52:42', '2023-01-20 08:52:42', NULL, 1, 1, '127.0.0.1', '127.0.0.1', NULL, 0, 1);

--
-- Triggers `edu_teacher`
--
DELIMITER $$
CREATE TRIGGER `after_delete_edu_teacher` AFTER DELETE ON `edu_teacher` FOR EACH ROW begin
DELETE FROM `edu_member_school` where `role` = 'T' and `member_id` = OLD.`teacher_id`;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `edu_test`
--

CREATE TABLE `edu_test` (
  `test_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `class` text DEFAULT NULL,
  `school_program_id` bigint(20) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `teacher_id` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `guidance` text DEFAULT NULL,
  `open` tinyint(1) DEFAULT 0,
  `has_limits` tinyint(1) DEFAULT 1,
  `trial_limits` int(11) DEFAULT 1,
  `threshold` float DEFAULT 75,
  `assessment_methods` enum('H','N') DEFAULT 'H',
  `number_of_question` int(11) DEFAULT 10,
  `number_of_option` int(11) DEFAULT 5,
  `question_per_page` int(11) DEFAULT 10,
  `random` tinyint(1) DEFAULT 1,
  `duration` bigint(20) DEFAULT 3600,
  `has_alert` tinyint(1) DEFAULT 1,
  `alert_time` bigint(20) DEFAULT 300,
  `alert_message` text DEFAULT NULL,
  `standard_score` double DEFAULT 1,
  `penalty` double DEFAULT 0,
  `order` int(11) DEFAULT NULL,
  `score_notification` tinyint(1) DEFAULT 0,
  `publish_answer` tinyint(1) DEFAULT 0,
  `time_answer_publication` datetime DEFAULT NULL,
  `test_availability` enum('F','L') DEFAULT 'F',
  `available_from` datetime DEFAULT NULL,
  `available_to` datetime DEFAULT NULL,
  `autosubmit` tinyint(1) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `member_create` bigint(20) DEFAULT NULL,
  `role_create` char(1) DEFAULT NULL,
  `member_edit` bigint(20) DEFAULT NULL,
  `role_edit` char(1) DEFAULT NULL,
  `ip_create` varchar(40) DEFAULT NULL,
  `ip_edit` varchar(40) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `edu_test`
--

INSERT INTO `edu_test` (`test_id`, `school_id`, `name`, `class`, `school_program_id`, `subject`, `teacher_id`, `description`, `guidance`, `open`, `has_limits`, `trial_limits`, `threshold`, `assessment_methods`, `number_of_question`, `number_of_option`, `question_per_page`, `random`, `duration`, `has_alert`, `alert_time`, `alert_message`, `standard_score`, `penalty`, `order`, `score_notification`, `publish_answer`, `time_answer_publication`, `test_availability`, `available_from`, `available_to`, `autosubmit`, `time_create`, `time_edit`, `member_create`, `role_create`, `member_edit`, `role_edit`, `ip_create`, `ip_edit`, `active`) VALUES
(1, 1, 'Test2', '', 0, '', '', '', 'Pilihlah jawaban yang paling tepat!', 0, 1, 1, 75, 'H', 10, 5, 10, 1, 3600, 1, 300, 'Lihat waktu sisa Anda. Silakan periksa kembali jawaban Anda. Segera kirimkan jawaban sebelum waktunya habis.', 1, 0, 0, 0, 0, NULL, 'F', NULL, NULL, 0, '2023-01-20 14:33:57', '2023-01-20 14:33:57', 1, 'A', 1, 'A', '127.0.0.1', '127.0.0.1', 1);

--
-- Triggers `edu_test`
--
DELIMITER $$
CREATE TRIGGER `after_delete_edu_test` AFTER DELETE ON `edu_test` FOR EACH ROW begin
DELETE FROM `edu_question` where `edu_question`.`test_id` = OLD.`test_id`;


DELETE FROM `edu_answer` where `edu_answer`.`test_id` = OLD.`test_id`;


DELETE FROM `edu_test_member` where `edu_test_member`.`test_id` = OLD.`test_id`;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `edu_test_collection`
--

CREATE TABLE `edu_test_collection` (
  `test_collection_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `number_of_question` int(11) DEFAULT 0,
  `number_of_option` int(11) DEFAULT 0,
  `grade_id` bigint(20) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `file_path` varchar(50) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `file_md5` varchar(45) DEFAULT NULL,
  `file_sha1` varchar(45) DEFAULT NULL,
  `time_create` datetime DEFAULT NULL,
  `time_edit` datetime DEFAULT NULL,
  `ip_create` varchar(45) DEFAULT NULL,
  `ip_edit` varchar(45) DEFAULT NULL,
  `taken` bigint(20) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `edu_test_member`
--

CREATE TABLE `edu_test_member` (
  `test_member_id` bigint(20) NOT NULL,
  `school_id` bigint(20) DEFAULT NULL,
  `student_id` bigint(20) DEFAULT NULL,
  `test_id` bigint(20) DEFAULT NULL,
  `sessions_id` varchar(32) DEFAULT NULL,
  `time_enter` datetime DEFAULT NULL,
  `ip_enter` varchar(45) DEFAULT NULL,
  `time_exit` datetime DEFAULT NULL,
  `ip_exit` varchar(45) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `member_edit` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` bigint(20) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `username` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `phone_code` varchar(10) DEFAULT NULL,
  `phone` varchar(45) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `birth_place` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  `auth` varchar(45) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `background` text DEFAULT NULL,
  `circle_avatar` tinyint(1) DEFAULT 0,
  `picture_hash` varchar(4) DEFAULT NULL,
  `picture_crop_position` varchar(45) DEFAULT NULL,
  `img_360_compress` tinyint(1) DEFAULT 0,
  `show_compass` tinyint(1) DEFAULT 1,
  `autoplay_360` tinyint(1) DEFAULT 0,
  `autorotate_360` int(11) DEFAULT 0,
  `following` bigint(20) DEFAULT NULL,
  `follower` bigint(20) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `country_id` varchar(3) DEFAULT NULL,
  `state_id` bigint(20) DEFAULT NULL,
  `city_id` bigint(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `time_register` datetime DEFAULT NULL,
  `last_activity_ip` varchar(45) DEFAULT NULL,
  `last_activity_time` datetime DEFAULT NULL,
  `last_update_avatar_time` datetime DEFAULT NULL,
  `last_seen_ip` varchar(45) DEFAULT NULL,
  `last_seen_time` datetime DEFAULT NULL,
  `confirmed` tinyint(1) DEFAULT 0,
  `blocked` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`member_id`, `name`, `username`, `email`, `phone_code`, `phone`, `gender`, `birth_day`, `birth_place`, `password`, `auth`, `url`, `background`, `circle_avatar`, `picture_hash`, `picture_crop_position`, `img_360_compress`, `show_compass`, `autoplay_360`, `autorotate_360`, `following`, `follower`, `language`, `country_id`, `state_id`, `city_id`, `state`, `city`, `time_register`, `last_activity_ip`, `last_activity_time`, `last_update_avatar_time`, `last_seen_ip`, `last_seen_time`, `confirmed`, `blocked`, `active`) VALUES
(1, 'admin', 'admin', 'admin@planetbiru.com', '', '', 'M', '1983-12-10', 'Jambi', 'c3284d0f94606de1fd2af172aba15bf3', '', '', '', 0, '', '', 0, 1, 0, 0, 0, 0, 'en', 'ID', 0, 0, 'DKI Jakarta', 'Jakarta Barat', '2017-10-14 00:00:00', '', '2017-10-14 00:00:00', '2017-10-14 00:00:00', '127.0.0.1', '2017-10-14 00:00:00', 0, 0, 1),
(34, 'Budi Raharjo', 'BudiRaharjo', 'st_1011199_1@planetbiru.com', NULL, '08123456781', 'M', '2135-11-17', NULL, '593024eb40414ef9ec82f4560a6c31bb', 'fe1dd344473150ac7b157ff0638d1ca0', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(35, 'Muhammad Ramadhan', 'MuhammadRamadhan', 'st_1011200_1@planetbiru.com', NULL, '08123456782', 'M', '2135-11-18', NULL, 'c01806a97bcbb7cca8c23e2559117567', '7ef921fd04031e7f6139c5f73b9d1bce', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(36, 'Agung Ramadhan', 'AgungRamadhan', 'st_1011201_1@planetbiru.com', NULL, '08123456783', 'M', '2135-11-19', NULL, 'fabdeb91fd922f7c8eb3494e8ec57e33', 'd44f68eba541b1682b0822457c7728c4', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(37, 'Sinta Anjani', 'SintaAnjani', 'st_1011202_1@planetbiru.com', NULL, '08123456784', 'W', '2135-11-20', NULL, '60e73647bff21ab8bf425bf3fbe98ecd', 'ec0ff43037813a2ef4dabe5ab482d8e8', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(38, 'Siviasari', 'Siviasari', 'st_1011203_1@planetbiru.com', NULL, '08123456785', 'W', '2135-11-21', NULL, 'ede8a1181837641fa8e4517b35ef0baa', 'aa03dced3c65aa9a684b5dbca15212c4', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(39, 'Veni Ananda', 'VeniAnanda', 'st_1011204_1@planetbiru.com', NULL, '08123456786', 'W', '2135-11-22', NULL, 'f7fa364975f44cce9321dff3760ea377', '5dbdc357dac13fee745f38aefd65c0e7', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(40, 'Mukhlis', 'Mukhlis', 'st_1011205_1@planetbiru.com', NULL, '08123456787', 'M', '2135-11-23', NULL, '15bdac02e0b283acc24b74103110dad0', '55fa261eb5c25c001cc2da14b45e5044', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(41, 'Bimantara', 'Bimantara', 'st_1011206_1@planetbiru.com', NULL, '08123456788', 'M', '2136-11-24', NULL, '876f5e00a10a9461299de4cf3822ded8', 'a2c0ccee40efd7fa5838f9ca149de0b5', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(42, 'Dewi Permatasari', 'DewiPermatasari', 'st_1011207_1@planetbiru.com', NULL, '08123456789', 'W', '2136-11-25', NULL, '95c7bdb937eb4d5b1fb2a11c9962afb4', '6ba28a152cd8722f0bfb7582af53e996', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(43, 'Jaka Umbara', 'JakaUmbara', 'st_1011208_1@planetbiru.com', NULL, '08123456790', 'M', '2136-11-26', NULL, '8ad975cc47ee53a1d10e694b5c06426a', 'fcdfc645707321a8e8abf9a810ccdc37', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(44, 'Nona Melati', 'NonaMelati', 'st_1011209_1@planetbiru.com', NULL, '08123456791', 'W', '2136-11-27', NULL, '8dd2706a358ba9fc9a55844886591272', 'cd42185b44e53af2f066bee26ff97435', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(45, 'Ardianto', 'Ardianto', 'st_1011210_1@planetbiru.com', NULL, '08123456792', 'M', '2136-11-28', NULL, '4f9d01d8b3fcdf76bfd1bf4c01781552', 'bf9d579f595fbb733d9a768b8657a482', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(46, 'Angga Permana', 'AnggaPermana', 'st_1011211_1@planetbiru.com', NULL, '08123456793', 'M', '2136-11-29', NULL, 'ea6c1a8f5d8bd671ae5f92afdb3190e1', 'a257584d8aa93b8b85b104f909891b7d', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(47, 'Ratnasari', 'Ratnasari', 'st_1011212_1@planetbiru.com', NULL, '08123456794', 'W', '2136-11-30', NULL, '58159cca9547b1c83e8b1d6dceb3f38c', '6aff8b11a300e6097f4e5408c461dede', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(48, 'Anissa Widiya', 'AnissaWidiya', 'st_1011213_1@planetbiru.com', NULL, '08123456795', 'W', '2136-12-01', NULL, '4760491837a95dc7002e02568f8944bf', 'ff8a940d290434e375ea10d2a2e6ac66', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(49, 'Caca Melani', 'CacaMelani', 'st_1011214_1@planetbiru.com', NULL, '08123456796', 'W', '2137-12-02', NULL, '3363676ce59ecc75cf7346f26d0b6004', 'da2ec1d7ef63dbf6a307611ece0ac557', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(50, 'Tiara Permatasari', 'TiaraPermatasari', 'st_1011215_1@planetbiru.com', NULL, '08123456797', 'W', '2137-12-03', NULL, 'ffe59b17137e28ab147f885fd6776b43', '0db9c3a7014508e0ff92384b0729f26d', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(51, 'Cesilya', 'Cesilya', 'st_1011216_1@planetbiru.com', NULL, '08123456798', 'W', '2137-12-04', NULL, 'aa2fbde2108057b06d775f9d30e6ebd4', 'a16f8246390abeaff157d5c4bcbd588a', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(52, 'Paramitha', 'Paramitha', 'st_1011217_1@planetbiru.com', NULL, '08123456799', 'W', '2137-12-05', NULL, 'c20e84fab5ad9975d5389847139eeed3', '66d10d9ade88731d9ac0c3a6ca699593', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(53, 'Nina Kurniati', 'NinaKurniati', 'st_1011218_1@planetbiru.com', NULL, '08123456800', 'W', '2137-12-06', NULL, 'ec0d0791a5955f36a6e2a962cadc59c7', '15d227d87e3b18b5eb956b754175aad2', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(54, 'Zahra Amanda', 'ZahraAmanda', 'st_1011219_1@planetbiru.com', NULL, '08123456801', 'W', '2137-12-07', NULL, '26df70307e38ff9a456dec76a05a2946', '7f04d061d9cb82ec4b7a4afab5a54d9d', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(55, 'Maulinda', 'Maulinda', 'st_1011220_1@planetbiru.com', NULL, '08123456802', 'W', '2137-12-08', NULL, '4d60898436031ac660d083a82403e15d', '14edca0490e9766f372f9dffa487406b', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(56, 'Dadang Sunarto', 'DadangSunarto', 'st_1011221_1@planetbiru.com', NULL, '08123456803', 'M', '2137-12-09', NULL, '67f366dfc3ef17d172b833569f30c346', '5fc8da7fd62028cebe0d3b27c3341e77', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(57, 'Didi Suyadi', 'DidiSuyadi', 'st_1011222_1@planetbiru.com', NULL, '08123456804', 'M', '2137-12-10', NULL, '07d6de2b356e2ffb378fd00382984f49', 'ec2ded15e7bc5750e642ba75c9a54957', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(58, 'Linda', 'Linda', 'st_1011223_1@planetbiru.com', NULL, '08123456805', 'W', '2137-12-11', NULL, 'f0559eb7a9f24bec5d3f34ca975c0c2d', '8f6649248fae3fac4901e9c43216384f', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(59, 'Sri Nurhayati', 'SriNurhayati', 'st_1011224_1@planetbiru.com', NULL, '08123456806', 'W', '2137-12-12', NULL, '6b42e5e8910c03d942cdb38e74b7bd11', 'c0545d01c59eef0c541cff0a068f6a43', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(60, 'Dini', 'Dini', 'st_1011225_1@planetbiru.com', NULL, '08123456807', 'W', '2137-12-13', NULL, '2d786e3c019fcd93b5fc37142285194d', '1d4f3cf0733a5a1f65a460f601d7e796', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(61, 'Dina', 'Dina', 'st_1011226_1@planetbiru.com', NULL, '08123456808', 'W', '2137-12-14', NULL, '5e322c279476dab1e1f4b64897754d79', '584d122e4b449c42478f77fa351c3c8a', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(62, 'Winda', 'Winda', 'st_1011227_1@planetbiru.com', NULL, '08123456809', 'W', '2137-12-15', NULL, '48d03866c7113399e2f8f29d3edb9d14', '085cfe587707c40dd2633568f4398ee9', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(63, 'Tamara', 'Tamara', 'st_1011228_1@planetbiru.com', NULL, '08123456810', 'W', '2137-12-16', NULL, '346760678fbf99f01334e6990a9ebf8f', 'fc24cd5c1c884b628dd95e9d446dd8a4', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(64, 'Clarissa', 'Clarissa', 'st_1011229_1@planetbiru.com', NULL, '08123456811', 'W', '2137-12-17', NULL, '909bad3b77d4ddd0968c240d589bf5fa', 'd0fc615ebf80f83f9228d523413e1687', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(65, 'Umar Bakri', 'UmarBakri', 'tc_29010101001_1@planetbiru.com', NULL, '0861131313', 'M', '2128-12-18', NULL, '850c5086ca0dd443d136d6555ed8abee', 'ae563c3b473da8518468f14991965b93', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(66, 'Mashudi', 'Mashudi', 'tc_29010101002_1@planetbiru.com', NULL, '0861131314', 'M', '2128-12-19', NULL, '5481815587456e07c6d5697dbee8b3e8', 'caed61e3fb04d32b31f48e0be2ff05d9', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(67, 'Bara Api', 'BaraApi', 'tc_29010101003_1@planetbiru.com', NULL, '0861131315', 'M', '2128-12-20', NULL, '5fc8f7fd09a91044c8cd55e25f5d4923', '77b3d1fcedae74b9dc10769e15528092', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(68, 'Widya', 'Widya', 'tc_29010101004_1@planetbiru.com', NULL, '0861131316', 'W', '2128-12-21', NULL, 'bf3cd8a0fbb0cdc26a8bbb36d3f022ef', 'a29b314d0f69c8a47920467791a6b2e6', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(69, 'Sudirman', 'Sudirman', 'tc_29010101005_1@planetbiru.com', NULL, '0861131317', 'M', '2128-12-22', NULL, 'a56cf89a870b1d8a3d20ae566ce0c63a', '959a5dc65e0053fb9ff3c4bc77feca2a', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(70, 'Mayang', 'Mayang', 'tc_29010101006_1@planetbiru.com', NULL, '0861131318', 'W', '2128-12-23', NULL, 'c503f9d733f38cfc215b5fd3204ad8b2', '2d650b4c136dd3a7fef8266797de5e3f', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(71, 'Renda', 'Renda', 'tc_29010101007_1@planetbiru.com', NULL, '0861131319', 'W', '2128-12-24', NULL, '1904d00e7751aaca05a43591f2a1cca3', 'f50fafc8f23a3925ecf98d246587fd9e', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(72, 'Raudah', 'Raudah', 'tc_29010101008_1@planetbiru.com', NULL, '0861131320', 'W', '2128-12-25', NULL, '6530c2748973121696bb69af1e50e0cb', '819c05f127301953007476c86cb375ef', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(73, 'Mutiara', 'Mutiara', 'tc_29010101009_1@planetbiru.com', NULL, '0861131321', 'W', '2128-12-26', NULL, '0edc3e24fafbdc2d4f1f5e35bef0c7de', '7b7683eaae28932ec567a088134f3a6c', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(74, 'Buladi', 'Buladi', 'tc_29010101010_1@planetbiru.com', NULL, '0861131322', 'M', '2128-12-27', NULL, '2a18018263081bfea9cba7283a9d7d0a', '0d22eaf7f501fb7376b100ffffe9b3ca', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(75, 'Sumijan', 'Sumijan', 'tc_29010101011_1@planetbiru.com', NULL, '0861131323', 'M', '2128-12-28', NULL, 'c72f6d4908f14d875fee1c2c44cb98a0', 'fa7a61f0bdbab54a02316da1143f5333', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(76, 'Mardianto', 'Mardianto', 'tc_29010101012_1@planetbiru.com', NULL, '0861131324', 'M', '2128-12-29', NULL, '352256378cb8253ae2abdd6b985c8951', '2004997b856f4d6826a1594e46e71486', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(77, 'Junaidi', 'Junaidi', 'tc_29010101013_1@planetbiru.com', NULL, '0861131325', 'M', '2128-12-30', NULL, '24766814f9c995d89f19c8582906ae36', '038351acb521183444b9d4fedf5ea01a', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(78, 'Aisyah', 'Aisyah', 'tc_29010101014_1@planetbiru.com', NULL, '0861131326', 'W', '2128-12-31', NULL, '8095a7cf74acbe2c15d74427eeb0d4ad', '840e18e55a4e3907f9e268e4f5834d71', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(79, 'Mawardi', 'Mawardi', 'tc_29010101015_1@planetbiru.com', NULL, '0861131327', 'M', '2129-01-01', NULL, '3e243ba18f016329f69db42448fdbc70', 'c57fa70b3034dd42edbd070f585c80a2', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(80, 'Muladi', 'Muladi', 'tc_29010101016_1@planetbiru.com', NULL, '0861131328', 'M', '2129-01-02', NULL, 'bfb219968d9256814ce70fc62f140977', 'f7942a52728c1af100f3c9978579456a', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(81, 'Srikandi', 'Srikandi', 'tc_29010101017_1@planetbiru.com', NULL, '0861131329', 'W', '2129-01-03', NULL, '1ec7172029f44958150b195642c832e7', '2e1d482f0c678a43c89d21e62702e4ef', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(82, 'Bambang', 'Bambang', 'tc_29010101018_1@planetbiru.com', NULL, '0861131330', 'M', '2129-01-04', NULL, '9e1c01102551c8fd1f7b46a244070a94', '17ec56de09c4c3d1d60b05358b775e84', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(83, 'Markisa', 'Markisa', 'tc_29010101019_1@planetbiru.com', NULL, '0861131331', 'W', '2129-01-05', NULL, '046af22180d2d536463dc2874154cc61', '1f6f395abe2b1683cc23b55c5d843ef7', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(84, 'Mawar', 'Mawar', 'tc_29010101020_1@planetbiru.com', NULL, '0861131332', 'W', '2129-01-06', NULL, '5fbfe33ab64daf9a3acaa7b67ff3297b', '18a7aaf5ac13e31608cf4a3b2a1316a4', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(85, 'Rukman', 'Rukman', 'tc_29010101021_1@planetbiru.com', NULL, '0861131333', 'M', '2129-01-07', NULL, '7117c7691a2c5e50dc88da71bbf06d00', 'c5b2a84853e1f7e7f72580375d3d62e9', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(86, 'Dahlan', 'Dahlan', 'tc_29010101022_1@planetbiru.com', NULL, '0861131334', 'M', '2129-01-08', NULL, 'ac8a417ec08e732daa74f688056cd0d7', '43c2e6e721f42cfdd45fc2b5019ff46d', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1),
(87, 'Subekti', 'Subekti', 'tc_29010101023_1@planetbiru.com', NULL, '0861131335', 'M', '2129-01-09', NULL, '306b7639d65fed66cfa86b253dbafcbe', '0ce895f7a6f34686b48b0c89d0301eb1', NULL, NULL, 0, NULL, NULL, 0, 1, 0, 0, NULL, NULL, 'en', 'ID', NULL, NULL, NULL, NULL, '2023-01-20 08:52:42', '127.0.0.1', '2023-01-20 08:52:42', NULL, '127.0.0.1', '2023-01-20 08:52:42', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(100) NOT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `data` longtext DEFAULT NULL,
  `xdata` text DEFAULT NULL,
  `timestamp` bigint(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=REDUNDANT;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `ip`, `data`, `xdata`, `timestamp`) VALUES
('50b4c4181d5397d3008d103778f4b00f', '192.168.137.1', '', 'n:2:{f:8:\"hfreanzr\";f:6:\"Nvflnu\";f:8:\"cnffjbeq\";f:32:\"sspn5qq5s29436opp40681n38nq9qqs9\";}', 1505782061),
('b7edf2a7fa69b10408e47bf9b00e67e4', '192.168.137.1', '', 'n:2:{f:8:\"hfreanzr\";f:6:\"Nvflnu\";f:8:\"cnffjbeq\";f:32:\"sspn5qq5s29436opp40681n38nq9qqs9\";}', 1505781542),
('4f2a9efcc8155a612de81356267181e6', '192.168.0.41', '', 'n:5:{f:8:\"hfreanzr\";f:6:\"Nvflnu\";f:8:\"cnffjbeq\";f:32:\"2pnr5rrrnp439q18rrq87899n62r2214\";f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";f:15:\"fhccbeg_purpxrq\";v:1;}', 1506297859),
('33a29a434afa4fc0eefd9033a0b304fc', '192.168.0.40', '', 'n:9:{f:16:\"fghqrag_hfreanzr\";f:13:\"NthatEnznquna\";f:16:\"fghqrag_cnffjbeq\";f:32:\"r10nqp3949on59noor56r057s20s883r\";f:12:\"frffvba_grfg\";n:0:{}f:10:\"nafjre_gzc\";n:1:{v:6;n:0:{}}f:16:\"grnpure_hfreanzr\";f:6:\"Nvflnu\";f:16:\"grnpure_cnffjbeq\";f:32:\"0qroqr2qo8sp3q09por4q6p2351760r6\";f:6:\"pheqve\";f:18:\"fpubby/1/negvpyr/2\";f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";}', 1506449059),
('15a4cb9922709227bcdaaa565958e6b4', '192.168.0.41', '', 'n:3:{f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";f:15:\"fhccbeg_purpxrq\";v:1;}', 1506296849),
('2bf89f19c17510ca13d7895210647dc0', '192.168.0.40', '', 'n:3:{f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";f:6:\"pheqve\";f:18:\"fpubby/1/negvpyr/2\";}', 1506611866),
('5bc02788ee20e4c5af8511375d9655f8', '192.168.137.1', '', 'n:5:{f:16:\"grnpure_hfreanzr\";f:6:\"Nvflnu\";f:16:\"grnpure_cnffjbeq\";f:32:\"0qroqr2qo8sp3q09por4q6p2351760r6\";f:6:\"pheqve\";f:15:\"fpubby/1/grfg/2\";f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";}', 1506899745),
('c1733d0d69273e48cfafc9a18ecea457', '192.168.137.1', '', 'n:3:{f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";f:6:\"pheqve\";f:20:\"fpubby/1/qrfpevcgvba\";}', 1506731222),
('ebecaa83f44d9ec3baab4bfd3a523cc6', '192.168.0.40', '', 'n:5:{f:14:\"nqzva_hfreanzr\";f:5:\"nqzva\";f:14:\"nqzva_cnffjbeq\";f:32:\"21232s297n57n5n743894n0r4n801sp3\";f:16:\"grnpure_hfreanzr\";f:6:\"Nvflnu\";f:16:\"grnpure_cnffjbeq\";f:32:\"0qroqr2qo8sp3q09por4q6p2351760r6\";f:6:\"pheqve\";f:15:\"fpubby/1/grfg/2\";}', 1506732150);

-- --------------------------------------------------------

--
-- Table structure for table `state`
--

CREATE TABLE `state` (
  `state_id` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `country_id` varchar(3) NOT NULL,
  `state_code` varchar(7) DEFAULT NULL,
  `subdivision_code` varchar(7) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `verify` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `state`
--

INSERT INTO `state` (`state_id`, `name`, `country_id`, `state_code`, `subdivision_code`, `type`, `verify`, `active`) VALUES
(1667, 'Bali', 'ID', 'ID-BA', '', '111', 1, 1),
(1668, 'Bangka Belitung', 'ID', 'ID-BB', 'ID-SM', '111', 1, 1),
(1669, 'Banten', 'ID', 'ID-BT', 'ID-JW', '111', 1, 1),
(1670, 'Bengkulu', 'ID', 'ID-BE', 'ID-SM', '111', 1, 1),
(1672, 'Gorontalo', 'ID', 'ID-GO', '', '111', 1, 1),
(1674, 'Jambi', 'ID', 'ID-JA', 'ID-SM', '111', 1, 1),
(1675, 'Jawa Barat', 'ID', 'ID-JB', 'ID-JW', '111', 1, 1),
(1676, 'Jawa Tengah', 'ID', 'ID-JT', 'ID-JW', '111', 1, 1),
(1677, 'Jawa Timur', 'ID', 'ID-JI', 'ID-JW', '111', 1, 1),
(1678, 'Kalimantan Barat', 'ID', 'ID-KB', 'ID-KA', '111', 1, 1),
(1679, 'Kalimantan Selatan', 'ID', 'ID-KS', 'ID-KA', '111', 1, 1),
(1680, 'Kalimantan Tengah', 'ID', 'ID-KT', 'ID-KA', '111', 1, 1),
(1681, 'Kalimantan Timur', 'ID', 'ID-KI', 'ID-KA', '111', 1, 1),
(1683, 'Lampung', 'ID', 'ID-LA', 'ID-SM', '111', 1, 1),
(1684, 'Maluku', 'ID', 'ID-MA', 'ID-ML', '111', 1, 1),
(1685, 'Maluku Utara', 'ID', 'ID-MU', 'ID-ML', '111', 1, 1),
(1686, 'Nusa Tenggara Barat', 'ID', 'ID-NB', 'ID-NU', '111', 1, 1),
(1687, 'Nusa Tenggara Timur', 'ID', 'ID-NT', 'ID-NU', '111', 1, 1),
(1688, 'Papua', 'ID', 'ID-PA', 'ID-PP', '111', 1, 1),
(1689, 'Riau', 'ID', 'ID-RI', 'ID-SM', '111', 1, 1),
(1690, 'Kepulauan Riau', 'ID', 'ID-KR', 'ID-SM', '111', 1, 1),
(1692, 'Sulawesi Selatan', 'ID', 'ID-SN', 'ID-SL', '111', 1, 1),
(1693, 'Sulawesi Tengah', 'ID', 'ID-ST', 'ID-SL', '111', 1, 1),
(1694, 'Sulawesi Tenggara', 'ID', 'ID-SG', 'ID-SL', '111', 1, 1),
(1695, 'Sulawesi Utara', 'ID', 'ID-SA', 'ID-SL', '111', 1, 1),
(1696, 'Sumatra Barat', 'ID', 'ID-SB', 'ID-SM', '111', 1, 1),
(1697, 'Sumatra Selatan', 'ID', 'ID-SS', 'ID-SM', '111', 1, 1),
(1698, 'Sumatra Utara', 'ID', 'ID-SU', 'ID-SM', '111', 1, 1),
(1699, 'Yogyakarta', 'ID', 'ID-YO', 'ID-JW', '111', 1, 1),
(4242, 'DKI Jakarta', 'ID', 'ID-JK', 'ID-JW', '111', 1, 1),
(4243, 'Sulawesi Barat', 'ID', 'ID-SR', 'ID-SL', '111', 1, 1),
(4244, 'Papua Barat', 'ID', 'ID-PB', 'ID-PP', '111', 1, 1),
(4246, 'Nangroe Aceh Darussalam', 'ID', 'ID-AC', 'ID-SM', '111', 1, 1),
(4265, 'Kalimantan Utara', 'ID', 'ID-KU', 'ID-KA', '111', 1, 1),
(4790, 'tanggerang selatan', 'ID', '', '', '', 0, 1),
(4794, 'Aceh', 'ID', '', '', '', 0, 1),
(4795, 'brebes jawa tengah', 'ID', '', '', '', 0, 1),
(4797, 'Depok', 'ID', '', '', '', 0, 1),
(4798, 'Indonesia', 'ID', '', '', '', 0, 1),
(4799, 'Jakarta Selatan', 'ID', '', '', '', 0, 1),
(4800, 'Jakarta timur', 'ID', '', '', '', 0, 1),
(4801, 'Indonesia,Cilacap,Jawa tengah', 'ID', '', '', '', 0, 1),
(4802, 'jambi timur', 'ID', '', '', '', 0, 1),
(4803, 'JakartaBarat', 'ID', '', '', '', 0, 1),
(4804, 'Jakarta', 'ID', '', '', '', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`city_id`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `edu_admin`
--
ALTER TABLE `edu_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `edu_answer`
--
ALTER TABLE `edu_answer`
  ADD PRIMARY KEY (`answer_id`);

--
-- Indexes for table `edu_article`
--
ALTER TABLE `edu_article`
  ADD PRIMARY KEY (`article_id`);

--
-- Indexes for table `edu_class`
--
ALTER TABLE `edu_class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `edu_info`
--
ALTER TABLE `edu_info`
  ADD PRIMARY KEY (`info_id`);

--
-- Indexes for table `edu_mail_list`
--
ALTER TABLE `edu_mail_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `edu_member_school`
--
ALTER TABLE `edu_member_school`
  ADD PRIMARY KEY (`member_id`,`school_id`,`role`);

--
-- Indexes for table `edu_option`
--
ALTER TABLE `edu_option`
  ADD PRIMARY KEY (`option_id`);

--
-- Indexes for table `edu_question`
--
ALTER TABLE `edu_question`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `edu_school`
--
ALTER TABLE `edu_school`
  ADD PRIMARY KEY (`school_id`),
  ADD UNIQUE KEY `nama` (`name`);

--
-- Indexes for table `edu_school_program`
--
ALTER TABLE `edu_school_program`
  ADD PRIMARY KEY (`school_program_id`);

--
-- Indexes for table `edu_school_response`
--
ALTER TABLE `edu_school_response`
  ADD PRIMARY KEY (`school_response_id`);

--
-- Indexes for table `edu_student`
--
ALTER TABLE `edu_student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `edu_teacher`
--
ALTER TABLE `edu_teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `edu_test`
--
ALTER TABLE `edu_test`
  ADD PRIMARY KEY (`test_id`);

--
-- Indexes for table `edu_test_collection`
--
ALTER TABLE `edu_test_collection`
  ADD PRIMARY KEY (`test_collection_id`);

--
-- Indexes for table `edu_test_member`
--
ALTER TABLE `edu_test_member`
  ADD PRIMARY KEY (`test_member_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`state_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `city`
--
ALTER TABLE `city`
  MODIFY `city_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49035;

--
-- AUTO_INCREMENT for table `edu_admin`
--
ALTER TABLE `edu_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `edu_answer`
--
ALTER TABLE `edu_answer`
  MODIFY `answer_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edu_article`
--
ALTER TABLE `edu_article`
  MODIFY `article_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edu_class`
--
ALTER TABLE `edu_class`
  MODIFY `class_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `edu_info`
--
ALTER TABLE `edu_info`
  MODIFY `info_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `edu_mail_list`
--
ALTER TABLE `edu_mail_list`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edu_option`
--
ALTER TABLE `edu_option`
  MODIFY `option_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `edu_question`
--
ALTER TABLE `edu_question`
  MODIFY `question_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `edu_school`
--
ALTER TABLE `edu_school`
  MODIFY `school_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `edu_school_program`
--
ALTER TABLE `edu_school_program`
  MODIFY `school_program_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `edu_school_response`
--
ALTER TABLE `edu_school_response`
  MODIFY `school_response_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edu_student`
--
ALTER TABLE `edu_student`
  MODIFY `student_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `edu_teacher`
--
ALTER TABLE `edu_teacher`
  MODIFY `teacher_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `edu_test`
--
ALTER TABLE `edu_test`
  MODIFY `test_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `edu_test_collection`
--
ALTER TABLE `edu_test_collection`
  MODIFY `test_collection_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `edu_test_member`
--
ALTER TABLE `edu_test_member`
  MODIFY `test_member_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `state`
--
ALTER TABLE `state`
  MODIFY `state_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4805;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

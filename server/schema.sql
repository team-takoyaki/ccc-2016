CREATE DATABASE `db_kattte` DEFAULT CHARACTER SET utf8;

-- ユーザーテーブル
CREATE TABLE katte_user (
  id BIGINT UNSIGNED (20) AUTO_INCREMENT,
  user_name VARCHAR(255) NOT NULL UNIQUE,
  user_hash VARCHAR(255) NOT NULL,
  registration_id VARCHAR(255) NOT NULL,
  grade INTEGER NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX user_name_index(user_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 商品テーブル
CREATE TABLE katte_items (
  id BIGINT UNSIGNED (20) AUTO_INCREMENT,
  katte_user_id VARCHAR(255) NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  item_description VARCHAR(255) NOT NULL,
  is_purchased TINYINT(1) NOT NULL DEFAULT 0,
  mention_user_id BIGINT (20) DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREGIN KEY katte_user_id,
  PREFERENCES katte_user(id),
  INDEX item_name_index(item_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- mention 管理テーブル(katte_user_id がどれだけメンションされてるか見るため)
CREATE TABLE mention (
  id BIGINT UNSIGNED (20) AUTO_INCREMENT,
  from_katte_user_id BIGINT UNSIGNED (20) NOT NULL,
  to_katte_user_id BIGINT UNSIGNED (20) NOT NULL,
  item_id BIGINT UNSIGNED (20) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREGIN KEY to_katte_user_id,       /* to_katte_user_id に foregin 貼るのは残りどれくらいメンションできるかカウントするから*/
  PREFERENCES katte_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- extension から定期的に叩かれてどれくらいの通知が必要かどうかを返すための管理テーブル(current_timestamp - last_notification_at > notification_master.duration なら true 返す)
CREATE TABLE notification (
  id BIGINT UNSIGNED (20) AUTO_INCREMENT,
  katte_user_id BIGINT UNSIGNED (20) NOT NULL,
  notification_master_id INTEGER UNSIGNED NOT NULL,
  last_notification_at VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREGIN KEY katte_user_id,
  PREFERENCES katte_user(id),
  INDEX user_id_last_notification_at_index(katte_user_id, last_notification_at),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- grade ごとの mention できるカウント数の管理テーブル
CREATE TABLE max_mention_count_by_grade (
  id INTEGER AUTO_INCREMENT,
  grade INTEGER DEFAULT 0,
  max_mention_count INTEGER DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX grade_index(grade), /* grade で検索したいから(レコード少なすぎるからフルスキャンでも問題はない) */
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO max_mention_count_by_grade SET grade = 1, max_mention_count = 3;
INSERT INTO max_mention_count_by_grade SET grade = 2, max_mention_count = 10;
INSERT INTO max_mention_count_by_grade SET grade = 3, max_mention_count = 50;
INSERT INTO max_mention_count_by_grade SET grade = 4, max_mention_count = 150;
INSERT INTO max_mention_count_by_grade SET grade = 5, max_mention_count = -1; /* -1: 無制限 */

-- 通知間隔の管理テーブル
CREATE TABLE notification_master (
  id INTEGER UNSIGNED AUTO_INCREMENT,
  description VARCHAR(255) NOT NULL,
  message VARCHAR(255) NOT NULL,
  duration INTEGER DEFAULT 60,
  created_at TIMESTAMP NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO notificatino_master SET description = '1分置き', duration = 60, message = 'さっさと買えよばかやろう';
INSERT INTO notificatino_master SET description = '5分置き', duration = 300, message = 'いい加減買ったほうがいい';
INSERT INTO notificatino_master SET description = '10分置き', duration = 600, message = '購入するだけでこの通知は止まります';
INSERT INTO notificatino_master SET description = '60分置き', duration = 3600, message = 'あなたの購入を待っています';

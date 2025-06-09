CREATE TABLE `agenda_classes`
(
    `id`   smallint    NOT NULL AUTO_INCREMENT,
    `name` varchar(16) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `classes_pk` (`name`)
);

CREATE TABLE `agenda_status`
(
    `user_id` smallint                           NOT NULL,
    `todo_id` smallint                           NOT NULL,
    `status`  enum ('todo','in_progress','done') NOT NULL
);

CREATE TABLE `agenda_subjects`
(
    `id`         mediumint                                                                                 NOT NULL AUTO_INCREMENT,
    `class_id`   smallint                                                                                  NOT NULL,
    `name`       varchar(16)                                                                               NOT NULL,
    `type`       enum ('main','others','humas')                                                            NOT NULL,
    `color`      enum ('red','orange','yellow','green','blue','maroon','gray','lightgray','pink','purple') NOT NULL,
    `is_deleted` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`id`)
);

CREATE TABLE `agenda_todo`
(
    `id`             mediumint                             NOT NULL AUTO_INCREMENT,
    `class_id`       smallint                              NOT NULL,
    `creator_id`     smallint                              NOT NULL,
    `last_editor_id` smallint                                       DEFAULT NULL,
    `is_private`     tinyint(1)                            NOT NULL DEFAULT '0',
    `subject_id`     mediumint                             NOT NULL,
    `type`           enum ('report','practice','reminder') NOT NULL,
    `duedate`        date                                  NOT NULL,
    `content`        text                                  NOT NULL,
    `link`           text,
    PRIMARY KEY (`id`)
);

CREATE TABLE `links`
(
    `id`              mediumint                  NOT NULL AUTO_INCREMENT,
    `author_id`       smallint                   NOT NULL,
    `date`            timestamp                  NOT NULL DEFAULT (now()),
    `expiration_date` date                                DEFAULT NULL,
    `status`          enum ('normal','censored') NOT NULL DEFAULT 'normal',
    `title`           varchar(50)                NOT NULL,
    `description`     text,
    `link`            varchar(2048)              NOT NULL,
    `likes`           smallint                   NOT NULL DEFAULT '0',
    `dislikes`        smallint                   NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
);

CREATE TABLE `links_likes`
(
    `user_id` smallint                NOT NULL,
    `link_id` mediumint               NOT NULL,
    `type`    enum ('like','dislike') NOT NULL
);

CREATE TABLE `menu_subscriptions`
(
    `ri_lunch`    tinyint(1)                             NOT NULL DEFAULT '1',
    `ri_dinner`   tinyint(1)                             NOT NULL DEFAULT '1',
    `ri_weekend`  tinyint(1)                             NOT NULL DEFAULT '1',
    `olivier`     tinyint(1)                             NOT NULL DEFAULT '1',
    `lunch_time`  enum ('11:10','11:30','12:00','12:30') NOT NULL DEFAULT '11:10',
    `dinner_time` enum ('17:10','17:30','18:00','18:30') NOT NULL DEFAULT '17:10',
    `endpoint`    varchar(512)                           NOT NULL,
    `key_p256dh`  varchar(128)                           NOT NULL,
    `key_auth`    varchar(22)                            NOT NULL,
    UNIQUE KEY `menu_subscriptions_pk` (`key_p256dh`, `key_auth`)
);

CREATE TABLE `users`
(
    `id`                      smallint                                  NOT NULL AUTO_INCREMENT,
    `name`                    varchar(64)                               NOT NULL COMMENT '64 email max length',
    `email_date`              datetime                                           DEFAULT NULL,
    `email_token`             varchar(32)                                        DEFAULT NULL,
    `email_code`              varchar(4)                                         DEFAULT NULL,
    `email_code_trials`       smallint                                  NOT NULL DEFAULT '0',
    `auth_token`              varchar(64)                                        DEFAULT NULL COMMENT 'Cookie token',
    `status`                  enum ('normal','email_disabled','banned') NOT NULL DEFAULT 'normal',
    `class_id`                smallint                                           DEFAULT NULL,
    `requested_class_id`      smallint                                           DEFAULT NULL,
    `email_resubscribe_token` varchar(64)                                        DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_pk` (`name`)
);
/

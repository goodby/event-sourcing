CREATE TABLE `event_store` (
  `event_id`       BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `stream_name`    VARCHAR(250) NOT NULL,
  `stream_version` INT(11)      NOT NULL,
  `event_type`     VARCHAR(250) NOT NULL,
  `event_body`     TEXT         NOT NULL,
  KEY (`stream_name`),
  UNIQUE KEY (`stream_name`, `stream_version`),
  PRIMARY KEY (`event_id`)
)
  ENGINE =InnoDB;

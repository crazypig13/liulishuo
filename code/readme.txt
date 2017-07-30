【2016-3-16】
CREATE TABLE `cv_resume_ey` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`resume_id` INT(11) NOT NULL DEFAULT '0',
	`identification` VARCHAR(20) NULL DEFAULT NULL COMMENT '身份证',
	`questionaire` VARCHAR(2000) NULL DEFAULT NULL COMMENT '调查问卷',
	`talk` VARCHAR(1000) NULL DEFAULT NULL COMMENT '选场次',
	PRIMARY KEY (`id`),
	INDEX `resume_id` (`resume_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB

[2016-3-1.2]
安永的admin端，resume相关接口改动
/demo/admin/resumes 增加 talk、 modified(是否被修改)
/demo/admin/resumes/:id 增加 talk、  questionaire
在对应的安永库中执行
ALTER TABLE `cv_resume_read` ADD COLUMN `raw_time` DATETIME NULL DEFAULT NULL

[2016-3-1]
安永的app端，增加远程存储接口
/demo/app/wx/storage
/demo/app/wx/js2
模拟测试时session默认为 {"openid":"10087","nickname":"viola"}
在对应的安永库中执行
ALTER TABLE `cv_wx_user` ADD COLUMN `storage` VARCHAR(2000) NULL DEFAULT NULL
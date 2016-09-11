DROP TABLE IF EXISTS "user" CASCADE;

CREATE SEQUENCE "user_seq" RESTART;

CREATE TABLE "user"
(
	"ID" int NOT NULL DEFAULT nextval('user_seq'),
	"Email" varchar(127) NOT NULL,
	"Name" varchar(255) NOT NULL,
	"Password" varchar(255) NOT NULL,
	"Group_ID" int NOT NULL,
	"Active" boolean NOT NULL DEFAULT false,
	CONSTRAINT "user_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "user_UN_0" UNIQUE ("Email"),
	CONSTRAINT "user_UN_1" UNIQUE ("Name"),
	CONSTRAINT "user_FK_Group_ID" FOREIGN KEY ("Group_ID")
		REFERENCES "group" ("ID") ON DELETE CASCADE

);

ALTER SEQUENCE "user_seq" OWNED BY "user"."ID";

COMMENT ON TABLE "user" IS 'Пользователь';

COMMENT ON COLUMN "user"."ID" IS 'Порядковый номер';
COMMENT ON COLUMN "user"."Email" IS 'Почтовый ящик';
COMMENT ON COLUMN "user"."Name" IS 'Имя';
COMMENT ON COLUMN "user"."Password" IS 'Пароль';
COMMENT ON COLUMN "user"."Group_ID" IS 'Привязка к группе';
COMMENT ON COLUMN "user"."Active" IS 'Активность';
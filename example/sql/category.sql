CREATE SEQUENCE "category_seq" RESTART;

CREATE TABLE "category"
(
	"ID" int NOT NULL DEFAULT nextval('category_seq'),
	"Name" varchar(255) NOT NULL,
	"Url" varchar(255) NOT NULL,
	"Order" int NOT NULL DEFAULT currval('category_seq'),
	"Parent" int NULL,
	CONSTRAINT "category_PK" PRIMARY KEY ("ID"),
	CONSTRAINT "category_category_FK_Parent" FOREIGN KEY ("Parent")
		REFERENCES "category" ("ID") ON DELETE CASCADE

);

ALTER SEQUENCE "category_seq" OWNED BY "category"."ID";

CREATE UNIQUE INDEX "category_UN1" ON "category" ("Name", "Parent") WHERE "Parent" IS NOT NULL;
CREATE UNIQUE INDEX "category_UN1_NULL" ON "category" ("Name") WHERE "Parent" IS NULL;
CREATE UNIQUE INDEX "category_UN2" ON "category" ("Url", "Parent") WHERE "Parent" IS NOT NULL;
CREATE UNIQUE INDEX "category_UN2_NULL" ON "category" ("Url") WHERE "Parent" IS NULL;

COMMENT ON TABLE "category" IS 'Категория';

COMMENT ON COLUMN "category"."ID" IS 'Порядковый номер';
COMMENT ON COLUMN "category"."Name" IS 'Наименование';
COMMENT ON COLUMN "category"."Url" IS 'Урл';
COMMENT ON COLUMN "category"."Order" IS 'Сортировка';
COMMENT ON COLUMN "category"."Parent" IS 'Корень';
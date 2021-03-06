# excavator

Pull and deploy artifacts from S3

[![CircleCI](https://circleci.com/gh/aautar/excavator.svg?style=shield)](https://circleci.com/gh/aautar/excavator)
[![codecov](https://codecov.io/gh/aautar/excavator/branch/master/graph/badge.svg)](https://codecov.io/gh/aautar/excavator)

## Usage

Set the following environment variables:

 - `S3_PATH`<br>(e.g. `s3://access:secret@region.bucket`)
 - `ARTIFACT_PATH_TEMPLATE`<br>(e.g. `deploy/artifact-release-%tag%.zip`)
 - `DB_MIGRATION_PATH_TEMPLATE`<br>(e.g. `sql/%dbname%-migration-%tag%.sql`)
 - `DB_CONNECTION`<br>(e.g. `mysql://root:rootpass@localhost:3306/mydb`)

Run script:

`php excavator [VERSION-TAG] [DESTINATION-FOLDER]`

Excavator will download the artifact, unzip it, attempt to run DB migration script (if present), and then place the unzipped files in the destination folder.

`DB_CONNECTION` is optional and no database migrations will be attempted if the environment variable is not present.

## Limitations
- For database migrations, only MySQL is supported
- Excavator will add and overwrite files, but not remove any existing files in the destination that are not in the artifact

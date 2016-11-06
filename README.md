# excavator

Pull and deploy artifacts from S3

[![CircleCI](https://circleci.com/gh/aautar/excavator.svg?style=shield)](https://circleci.com/gh/aautar/excavator)
[![codecov](https://codecov.io/gh/aautar/excavator/branch/master/graph/badge.svg)](https://codecov.io/gh/aautar/excavator)

###Usage

Set the following environment variables:

 - `S3_BUCKET`
 - `S3_ACCESS_KEY`
 - `S3_SECRET_KEY`
 - `S3_REGION`

Run script:

`php excavator [ARTIFACT-ZIP] [DESTINATION-FOLDER]`

Excavator will download the artifact, unzip it, and place the unzipped files in the destination folder

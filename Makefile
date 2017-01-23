BRANCH?=`git rev-parse --abbrev-ref HEAD`
COMMIT?=`git rev-parse HEAD`

all: build

build:
	@docker build --build-arg BRANCH=${BRANCH} --build-arg COMMIT=${COMMIT} --tag=jdel/sspks .

release: build
	@docker build --tag=jdel/sspks:$(shell cat VERSION) .

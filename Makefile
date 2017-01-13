BRANCH?=master

all: build

build:
	@docker build --build-arg BRANCH=${BRANCH} --tag=jdel/sspks .

release: build
	@docker build --tag=jdel/sspks:$(shell cat VERSION) .
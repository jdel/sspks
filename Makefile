BRANCH?=`git rev-parse --abbrev-ref HEAD`
COMMIT?=`git rev-parse HEAD`
DATE=`date "+%Y%m%d"`

.PHONY: all
all: build spk clean
	
.PHONY: spk
spk: sspks_noarch_${DATE}.spk

.PHONY: build
build:
	@docker build --build-arg BRANCH=${BRANCH} --build-arg COMMIT=${COMMIT} --tag=jdel/sspks .

sspks_noarch_${DATE}.spk: package.tgz INFO
	@cd ./_syno_package && COPYFILE_DISABLE=1 tar cfv ../sspks_noarch_${DATE}.spk --exclude='./package' *

package.tgz: .htaccess
	@rm -rf ./_syno_package/package/share/sspks/*
	@rsync -av --delete ./* ./_syno_package/package/share/sspks \
	       --exclude _syno_package \
	       --exclude docker \
	       --exclude hooks \
				 --exclude Dockerfile \
				 --exclude INSTALL.md \
				 --exclude README.md \
				 --exclude Makefile \
				 --exclude VERSION \
				 --exclude CHANGELOG \
				 --exclude phpunit.xml.dist \
				 --exclude tests
	@cd ./_syno_package/package && COPYFILE_DISABLE=1 tar cfvz ../package.tgz *

.htaccess:
	@mkdir -p ./_syno_package/package/share/sspks
	@echo "DirectoryIndex index.php" > ./_syno_package/package/share/sspks/.htaccess

INFO: VERSION
	@sed -i "s/version=\"\"/version=\"$(shell cat VERSION)\"/" ./_syno_package/INFO

.PHONY: clean
clean:
	@sed -i "s/version=\"$(shell cat VERSION)\"/version=\"\"/" ./_syno_package/INFO
	@rm -rf ./_syno_package/package/share/sspks/ ./_syno_package/package.tgz ./sspks_noarch_${DATE}.spk

.PHONY: release
release: build
	@docker build --tag=jdel/sspks:$(shell cat VERSION) .

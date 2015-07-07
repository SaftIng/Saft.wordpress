default:
	@echo ""
	@echo "Saft.wordpress - CLI"
	@echo ""
	@echo "- make update - Install or update Saft and Saft.skeleton."
	@echo ""

update:
	@echo ""
	@echo "> Clear folders"
	rm -rf Saft
	mkdir Saft

	@echo ""
	@echo "> Fetch Saft and Saft.skeleton"
	cp composer.json Saft/composer.json
	cd Saft && composer update --ignore-platform-reqs

	@echo ""
	@echo "> Remove all .git folders from subfolders"
	cd Saft && rm `find ./ -name '.git'` -rf

	@echo ""
	@echo "> Remove obsolete files from subfolders"
	cd Saft && rm `find ./ -type f -name '*.dist'` -rf
	cd Saft && rm `find ./ -type f -name '*.gif'` -rf
	cd Saft && rm `find ./ -type f -name '*.html'` -rf
	cd Saft && rm `find ./ -type f -name '*.ini'` -rf
	cd Saft && rm `find ./ -type f -name '*.js'` -rf
	cd Saft && rm `find ./ -type f -name '*.json'` -rf
	cd Saft && rm `find ./ -type f -name '*.md'` -rf
	cd Saft && rm `find ./ -type f -name '*.nt'` -rf
	cd Saft && rm `find ./ -type f -name '*.out'` -rf
	cd Saft && rm `find ./ -type f -name '*Test.php'` -rf
	cd Saft && rm `find ./ -type f -name '*.phpt'` -rf
	cd Saft && rm `find ./ -type f -name '*.tiff'` -rf
	cd Saft && rm `find ./ -type f -name '*.ttl'` -rf
	cd Saft && rm `find ./ -type f -name '*.txt'` -rf
	cd Saft && rm `find ./ -type f -name '*.xhtml'` -rf
	cd Saft && rm `find ./ -type f -name '*.xml'` -rf
	cd Saft && rm `find ./ -type f -name '*.xsl'` -rf
	cd Saft && rm `find ./ -type f -name '*.yml'` -rf

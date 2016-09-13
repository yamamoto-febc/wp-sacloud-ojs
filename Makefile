NAME=wp-sacloud-ojs

default: clean all

all: 
	mkdir -p $(NAME)
	cp -r wp-sacloud-ojs.php wp-cli.php readme.txt screenshot-1.png classes lang script style tpl vendor $(NAME)
	zip -vr wp-sacloud-ojs.zip $(NAME)

clean:
	rm -rf $(NAME) wp-sacloud-ojs.zip

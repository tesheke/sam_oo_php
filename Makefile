
all:
	php8.3 index.php
	ls img_thumb/ | wc --lines

clean:
	rm -rf [0-9]*s.htm [0-9]*s_r.htm img_thumb sam.htm sam_r.htm

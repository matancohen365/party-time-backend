<?php
printf("finding files to delete..\n");

foreach( glob(__DIR__.DIRECTORY_SEPARATOR.'*.mp3') as $file ) {

    unlink($file);

    printf("%s was deleted..\n", $file);

}

printf("done.\n");
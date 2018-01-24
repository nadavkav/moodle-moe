podTemplate(label: 'php-template', cloud: 'kuberneties', containers: [
    containerTemplate(name: 'php', image: 'eu.gcr.io/sysbind-servers-1026/php:7.0-fpm', ttyEnabled: true, alwaysPullImage: true),
    containerTemplate(name: 'db', image: 'eu.gcr.io/sysbind-servers-1026/mariadb:10.0.31', envVars:[
        containerEnvVar(key: 'MYSQL_ROOT_PASSWORD', value: 'password'),
        containerEnvVar(key: 'MYSQL_USER', value: 'mariadb'),
        containerEnvVar(key: 'MYSQL_PASSWORD', value: 'password'),
        containerEnvVar(key: 'MYSQL_DATABASE', value: 'moodle'),
      ], ports: [portMapping(name: 'mysql', containerPort: 3306, hostPort: 3306)] ),
  ], 
    volumes: [secretVolume(secretName:'slaves-ssh-key', mountPath:'/home/jenkins/.ssh')]) {
    node('php-template') {     
        stage('Get PHP ready') {
            git branch: '$BRANCH_NAME', url: 'git@gitlab.sysbind.biz:Developers/moe-alt.git'
            container('php') {
                sh 'cp config-dist.php config.php'
                sh 'sed -i -e "s%= \'pgsql\'%= \'mariadb\'%" config.php'
                sh 'sed -i -e "s%http:/p/example.com/moodle%http://localhost%" -e "s%/home/example/moodledata%/home/jenkins/workspace/$JOB_BASE_NAME/$BRANCH_NAME/target/moodledata%" config.php'
                sh 'sed -i -e "s%= \'username\'%= \'mariadb\'%" config.php'
                sh 'sed -i -e "s%= \'http://example.com/moodle\'%= \'http://sysbind.test\'%" config.php'
                sh 'mkdir -p /home/jenkins/workspace/$JOB_BASE_NAME/$BRANCH_NAME/target/moodledata/phpunit_$BUILD_ID'
                sh 'sed -i -e "/require_once/i \\\\\\$CFG->phpunit_dataroot = \'\\/home\\/jenkins\\/workspace\\/$JOB_BASE_NAME\\/$BRANCH_NAME\\/target\\/moodledata\\/phpunit_$BUILD_ID\';" -e "/require_once/i \\\\\\$CFG->phpunit_prefix = \'p_\';" config.php'
                sh '/usr/bin/composer install'
            }
            container('db'){}
        }
        
        stage ('Run UnitTest') {
            container('php') {
                sh 'php admin/tool/phpunit/cli/init.php'
                sh 'vendor/bin/phpunit --log-junit results/phpunit/phpunit.xml || true'
                step([$class: 'JUnitResultArchiver', testResults: '**/phpunit/phpunit.xml,**/behatlog/*.xml'])
            }
        }
    }
}
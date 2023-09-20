pipeline {
    agent any
    stages {
        stage ("Verify Tooling")
        {
            steps {
                sh '''
                    docker version
                    docker info
                    docker compose version
                    curl --version
                '''
            }
        }
        stage("Prune Docker data") {
            steps {
                sh 'docker system prune -a --volumes -f'
            }
        }
        stage ("Start container and Run composer install") {
            steps {
                sh 'docker compose -f /var/jenkins_home/workspace/todoMaster/backend/docker-compose.yaml up -d'
                sh 'cd /var/jenkins_home/workspace/todoMaster/backend && composer install'
            }
        }
    }
}
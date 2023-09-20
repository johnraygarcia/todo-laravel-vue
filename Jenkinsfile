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
                sh 'docker compose -f /var/lib/jenkins/workspace/todoMaster-deployment/backend/docker-compose.yaml up -d'
                sh 'cd /var/lib/jenkins/workspace/todoMaster-deployment/backend/ && composer install'
            }
        }
    }
}
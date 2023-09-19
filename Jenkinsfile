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
                    jq --version
                '''
            }
        }
        stage("Prune Docker data") {
            steps {
                sh 'docker system prune -a --volumes -f'
            }
        }
        stage ("Start container") {
            steps {
                sh 'docker compose -f /var/lib/jenkins/workspace/todoMaster-deployment/backend/docker-compose.yaml'
                sh 'docker compose ps'
            }
        }
        stage ("Run Composer") {
            steps {
                sh 'cd ./backend/ && composer install'
            }
        }
    }
}
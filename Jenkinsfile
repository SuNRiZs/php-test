pipeline {
    agent {
        node {
            label 'ALT_Linux' 
        }
    }
    environment {
        DOCKERHUB_CREDENTIALS = credentials('docker-hub-credentials')
        SSH_KEY_PATH = '/home/admuser/.ssh/test/id_rsa'
        GITHUB_CREDENTIALS = credentials('github-credentials-id')
    }
    stages {
        stage('Clone repository') {
            steps {
                git(credentialsId: 'github-credentials-id', url: 'https://github.com/SuNRiZs/php-test.git', branch: 'main')
            }
        }
        stage('Add commit in site') {
            steps {
                sh """
                    sed -i "s/в 2024 году.*/в 2024 году сборка сайта \\№ ${BUILD_NUMBER}/" ./src/includes/_footer.php
                """
            }
        }
        stage('Build, Test and Push Image') {
            steps {
                script {
                    // Строим образ Docker и тегируем его номером сборки
                    def myImage = docker.build("sunraize/test-php:${BUILD_NUMBER}")
            
                    // Запускаем новый контейнер для тестирования
                    sh "docker run -d -p \"8080:80\" --name webtest sunraize/test-php:${BUILD_NUMBER}"
            
                    // Проверяем доступность веб-сервера в контейнере
                    def testResult = sh script: 'curl -f http://localhost:8080/', returnStatus: true
            
                    // Если тест не прошел, останавливаем и удаляем контейнер, затем выдаем ошибку
                    if (testResult != 0) {
                        sh "docker stop webtest || true"
                        sh "docker rm webtest || true"
                        // Удаляем образ на сборщике
                        sh "docker rmi sunraize/test-php:${BUILD_NUMBER}"
                        error('Тестирование образа завершилось неудачей')
                    }
            
                    // Если тест прошел успешно, отправляем образ в репозиторий
                    withCredentials([usernamePassword(credentialsId: 'docker-hub-credentials', usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD')]) {
                        sh "docker login -u ${USERNAME} -p ${PASSWORD}"
                        myImage.push("latest")
                        myImage.push("${env.BUILD_NUMBER}")
                        sh "docker logout"
                    }
                    // Останавливаем и удаляем тестовый контейнер
                    sh "docker stop webtest || true"
                    sh "docker rm webtest || true"
                }
            }
        }
        stage('Deploy to server') {
            steps {
                sh """
                    scp -i \${SSH_KEY_PATH} -P 40022 docker-compose.yml devops@194.190.221.21:/home/devops/
                    ssh -i \${SSH_KEY_PATH} -p 40022 devops@194.190.221.21 '
                    sed -i "s/image: sunraize\\/test-php:.*/image: sunraize\\/test-php:${BUILD_NUMBER}/" /home/devops/docker-compose.yml && docker-compose -f /home/devops/docker-compose.yml pull && docker-compose -f /home/devops/docker-compose.yml up -d --no-deps --build web
                    '
                """
            }
        }
        stage('Clean up Docker images') {
            steps {
                script {
                     sh "docker rmi sunraize/test-php:\${BUILD_NUMBER}"
                     sh """
                       ssh -i \${SSH_KEY_PATH} -p 40022 devops@194.190.221.21 '
                           docker images | grep "sunraize/test-php" | sort -r | tail -n +4 | awk "{print \\\\\$1\\\":\\\"\\\\\$2}" | xargs -r docker rmi
                        '
                     """
                }
            }
        }
    }
    post {
        always {
            cleanWs()
        }
    }
}

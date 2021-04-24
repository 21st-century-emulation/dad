docker build -q -t dad .
docker run --rm --name dad -d -p 127.0.0.1:8080:80 dad

sleep 5

RESULT=`curl -s --header "Content-Type: application/json" \
  --request POST \
  --data '{"id":"abcd", "opcode":9,"state":{"a":10,"b":51,"c":159,"d":5,"e":5,"h":161,"l":123,"flags":{"sign":false,"zero":true,"auxCarry":true,"parity":true,"carry":true},"programCounter":1,"stackPointer":2,"cycles":0}}' \
  http://localhost:8080/api/v1/execute`
EXPECTED='{"id":"abcd", "opcode":9,"state":{"a":10,"b":51,"c":159,"d":5,"e":5,"h":213,"l":26,"flags":{"sign":false,"zero":true,"auxCarry":true,"parity":true,"carry":false},"programCounter":1,"stackPointer":2,"cycles":10}}'

docker kill dad

DIFF=`diff <(jq -S . <<< "$RESULT") <(jq -S . <<< "$EXPECTED")`

if [ $? -eq 0 ]; then
    echo -e "\e[32mDAD Test Pass \e[0m"
    exit 0
else
    echo -e "\e[31mDAD Test Fail  \e[0m"
    echo "$RESULT"
    echo "$DIFF"
    exit -1
fi
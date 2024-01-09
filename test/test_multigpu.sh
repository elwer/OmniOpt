#!/bin/bash

#set -x

NUMGPUS=$1

if [[ "$NUMGPUS" -lt 2 ]]; then
        echo "test_multigpu.sh is not useful for less than 2 GPUs"
        exit 1
fi

echo "Trying multigpu.sh for $NUMGPUS GPUs"

JOBNAME=multigpu_test_${NUMGPUS}

RESULT=$(bash tools/multigpu.sh --jobname=$JOBNAME --partition=alpha --num_gpus=$NUMGPUS --force_redo --maxtime=00:05:00 --programfile=test/list_num_of_gpus.sh | grep Num | sed -e 's/.*: //')

echo $RESULT

if [[ "$RESULT" -eq "$NUMGPUS" ]]; then
        exit 0
else
        exit 1
fi

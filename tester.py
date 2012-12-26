def lineNum(num):
    if num > 0:
        return "- "+str(num)+"."
    return "-"

def start():
    import sys
    if len(sys.argv) != 3:
        print "Usage: ", sys.argv[0], "<file_path for file to test> <file_path for correct query output>"
        sys.exit()
    with open(sys.argv[2]) as f:
        contentL = f.readlines()
        content = set(contentL)
    with open(sys.argv[1]) as f:
        usercontent = f.readlines()
    if len(usercontent) > 0 and usercontent[0].startswith("Error"):
        print "".join(usercontent[:-3]),
    else:
        mismatch =  [lineNum(i-1)+" "+usercontent[i] for i in xrange(len(usercontent)-4) if usercontent[i] not in content]
        if len(mismatch) != 0:
            print "".join(mismatch),
        missing = ["* "+con for con in contentL[:-4] if con not in usercontent]
        if len(missing) != 0:
            print "".join(missing),

start()
